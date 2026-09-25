<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Pemilih;
use App\Models\PengaduanAkun;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class PengaduanController extends Controller
{
    /**
     * [PUBLIK] Form pengaduan akun terkunci/kendala login.
     */
    public function create()
    {
        return view('pengaduan.create');
    }

    /**
     * [PUBLIK] Submit pengaduan. Foto ktm WAJIB, dipakai admin sebagai
     * bukti identitas sebelum membuka kunci akun.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'foto_ktm' => ['required', 'image', 'max:2048'],
            'keterangan' => ['required', 'string', 'max:255'],
        ], [
            'no_hp.regex' => 'No. HP/WhatsApp hanya boleh berisi angka, tanpa huruf, spasi, atau tanda baca.',
        ]);

        $path = $request->file('foto_ktm')->store('pengaduan-ktm', 'public');

        $periodeAktif = Periode::aktif();

        PengaduanAkun::create([
            'identifier' => $validated['identifier'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'foto_ktm' => $path,
            'keterangan' => $validated['keterangan'],
            'periode_id' => $periodeAktif?->id,
            'status' => 'pending',
        ]);

        return redirect()->route('pengaduan.create')
            ->with('success', 'Pengaduan berhasil dikirim. Panitia akan memproses dan menghubungi Anda lewat email yang didaftarkan.');
    }

    /**
     * [ADMIN] Halaman gabungan: daftar akun terkunci (ambil langsung dari
     * data pivot, paling akurat) + daftar pengaduan masuk (untuk lihat
     * bukti ktm & kontak sebelum memutuskan). Digabung supaya admin
     * tidak perlu bolak-balik 2 halaman terpisah.
     */
    public function index()
    {
        $periodeTerbaru = Periode::latest('id')->first();

        $terkunci = $periodeTerbaru
            ? $periodeTerbaru->pemilihs()->wherePivot('status_akses', 'terkunci')->get()
            : collect();

        $pengaduans = PengaduanAkun::with(['periode', 'diprosesOleh'])
            ->orderByRaw("status = 'pending' desc")
            ->latest('id')
            ->paginate(20);

        return view('admin.pengaduan.index', compact('periodeTerbaru', 'terkunci', 'pengaduans'));
    }

    /**
     * [ADMIN] Buka kunci langsung dari daftar akun terkunci (tanpa lewat
     * pengaduan) -- dipakai kalau admin sudah yakin lewat cara lain (WA,
     * ketemu langsung, dsb), sama seperti PemilihController::unlock()
     * tapi disatukan di sini biar aksinya di satu halaman yang sama.
     */
    public function unlockLangsung(Request $request, Periode $periode, Pemilih $pemilih)
    {
        $updated = DB::table('pemilih_periode')
            ->where('periode_id', $periode->id)
            ->where('pemilih_id', $pemilih->id)
            ->where('status_akses', 'terkunci')
            ->update([
                'status_akses' => 'belum_voting',
                'percobaan_gagal' => 0,
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Akun ini tidak sedang dalam status terkunci.');
        }

        AuditLogAdmin::catat(
            'unlock_akun',
            "Membuka kunci akun {$pemilih->identifier} ({$pemilih->nama}) langsung dari halaman Akun Terkunci & Pengaduan.",
            $periode->id
        );

        return back()->with('success', "Akun {$pemilih->identifier} berhasil dibuka kembali.");
    }

    /**
     * [ADMIN] Detail satu pengaduan, termasuk cek status voting pengadu
     * (sudah coblos atau belum) sebelum admin memutuskan.
     */
    public function show(PengaduanAkun $pengaduan)
    {
        $pivot = $pengaduan->cariPivotPemilih();

        return view('admin.pengaduan.show', compact('pengaduan', 'pivot'));
    }

    /**
     * [ADMIN] Setujui pengaduan -> buka kunci akun (kalau memang terkunci
     * dan belum pernah voting). Ditolak otomatis kalau ternyata pengadu
     * sudah pernah submit suara -- tidak masuk akal membuka kunci akun
     * yang sudah dipakai.
     */
    public function setujui(Request $request, PengaduanAkun $pengaduan)
    {
        $pivot = $pengaduan->cariPivotPemilih();

        if (! $pivot) {
            // Bukan berarti gagal -- banyak pengaduan yang memang bukan soal
            // akun terkunci (mis. cuma lupa email/password API kampus, atau
            // sekadar tanya prosedur). Tetap approve pengaduannya supaya
            // tiketnya selesai, tapi TIDAK ada apapun yang di-unlock karena
            // memang tidak ada baris pemilih untuk di-unlock.
            $pengaduan->update([
                'status' => 'disetujui',
                'catatan_admin' => $request->input('catatan_admin') ?: 'Disetujui tanpa buka kunci (tidak ada baris pemilih ditemukan untuk periode ini).',
                'diproses_oleh' => auth('admin')->id(),
                'diproses_pada' => now(),
            ]);

            AuditLogAdmin::catat(
                'setujui_pengaduan',
                "Menyetujui pengaduan {$pengaduan->identifier} ({$pengaduan->nama}) TANPA buka kunci (pemilih tidak ditemukan di periode manapun).",
                $pengaduan->periode_id
            );

            return back()->with('success', "Pengaduan {$pengaduan->identifier} disetujui (tidak ada akun yang perlu dibuka kunci).");
        }

        if ($pivot->status_akses === 'sudah_voting') {
            return back()->with('error', 'Pemilih ini SUDAH pernah memberikan suara. Tidak bisa dibuka kunci / login ulang.');
        }

        DB::table('pemilih_periode')
            ->where('id', $pivot->id)
            ->update([
                'status_akses' => 'belum_voting',
                'percobaan_gagal' => 0,
                'updated_at' => now(),
            ]);

        $pengaduan->update([
            'status' => 'disetujui',
            'catatan_admin' => $request->input('catatan_admin'),
            'diproses_oleh' => auth('admin')->id(),
            'diproses_pada' => now(),
        ]);

        AuditLogAdmin::catat(
            'setujui_pengaduan',
            "Menyetujui pengaduan & membuka kunci akun {$pengaduan->identifier} ({$pengaduan->nama}).",
            $pengaduan->periode_id
        );

        return back()->with('success', "Pengaduan disetujui, akun {$pengaduan->identifier} berhasil dibuka kembali.");
    }

    /**
     * [ADMIN] Tolak pengaduan (mis. identitas tidak cocok, foto tidak jelas,
     * atau ternyata sudah pernah voting). Wajib isi alasan.
     */
    public function tolak(Request $request, PengaduanAkun $pengaduan)
    {
        $validated = $request->validate([
            'catatan_admin' => ['required', 'string'],
        ]);

        $pengaduan->update([
            'status' => 'ditolak',
            'catatan_admin' => $validated['catatan_admin'],
            'diproses_oleh' => auth('admin')->id(),
            'diproses_pada' => now(),
        ]);

        AuditLogAdmin::catat(
            'tolak_pengaduan',
            "Menolak pengaduan {$pengaduan->identifier} ({$pengaduan->nama}): {$validated['catatan_admin']}",
            $pengaduan->periode_id
        );

        return back()->with('success', 'Pengaduan ditolak.');
    }

    /**
     * [ADMIN] Buka blokir percobaan login untuk orang yang BELUM PERNAH
     * berhasil login sama sekali (jadi belum punya baris pemilih lokal,
     * tidak muncul di daftar Akun Terkunci biasa). Kuncinya cuma email,
     * sesuai throttle key yang dipakai AuthController::login().
     */
    public function bukaBlokirEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $key = 'login-pemilih:' . strtolower($validated['email']);
        RateLimiter::clear($key);

        AuditLogAdmin::catat(
            'buka_blokir_email',
            "Membuka blokir percobaan login untuk email {$validated['email']} (belum pernah login sukses sebelumnya)."
        );

        return back()->with('success', "Blokir untuk email {$validated['email']} berhasil dibuka.");
    }
}
