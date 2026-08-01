<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Pemilih;
use App\Models\PengaduanAkun;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * [PUBLIK] Submit pengaduan. Foto selfie WAJIB, dipakai admin sebagai
     * bukti identitas sebelum membuka kunci akun.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'foto_selfie' => ['required', 'image', 'max:2048'],
        ], [
            'no_hp.regex' => 'No. HP/WhatsApp hanya boleh berisi angka, tanpa huruf, spasi, atau tanda baca.',
        ]);

        $path = $request->file('foto_selfie')->store('pengaduan-selfie', 'public');

        $periodeAktif = Periode::aktif();

        PengaduanAkun::create([
            'identifier' => $validated['identifier'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'foto_selfie' => $path,
            'periode_id' => $periodeAktif?->id,
            'status' => 'pending',
        ]);

        return redirect()->route('pengaduan.create')
            ->with('success', 'Pengaduan berhasil dikirim. Panitia akan memproses dan menghubungi Anda lewat email yang didaftarkan.');
    }

    /**
     * [ADMIN] Daftar semua pengaduan, yang masih pending ditampilkan
     * paling atas supaya cepat ditindaklanjuti.
     */
    public function index()
    {
        $pengaduans = PengaduanAkun::with(['periode', 'diprosesOleh'])
            ->orderByRaw("status = 'pending' desc")
            ->latest('id')
            ->paginate(20);

        return view('admin.pengaduan.index', compact('pengaduans'));
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
            return back()->with('error', 'Pemilih ini tidak ditemukan terdaftar pada periode manapun. Tidak bisa dibuka kuncinya lewat sini.');
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
}
