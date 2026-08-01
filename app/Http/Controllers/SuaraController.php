<?php

namespace App\Http\Controllers;

use App\Models\AuditLogPresensi;
use App\Models\Kandidat;
use App\Models\Periode;
use App\Models\Suara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuaraController extends Controller
{
    /**
     * [PEMILIH] Tampilkan halaman pilih kandidat untuk periode aktif.
     * Dipisah dari store() karena ini cuma menampilkan form, bukan
     * memproses submit suara.
     */
    public function formVoting()
    {
        $periode = Periode::aktif();

        if (! $periode || $periode->status !== 'running') {
            return redirect()->route('login')->withErrors(['identifier' => 'Tidak ada pemilihan yang sedang berjalan saat ini.']);
        }

        $kandidats = $periode->kandidats()->orderBy('nomor_urut')->get();

        return view('voting.index', compact('periode', 'kandidats'));
    }

    /**
     * [PEMILIH] Submit satu suara. Wajib login lewat guard 'pemilih'
     * (lihat AuthController::login()). Validasi periode berstatus running,
     * kandidat memang milik periode aktif, dan penyimpanan suara dilakukan
     * dalam DB transaction supaya atomic — termasuk update status pemilih
     * jadi "sudah_voting" pada transaction yang sama.
     */
    public function store(Request $request)
    {
        // 1. Wajib sudah login sebagai pemilih.
        if (! Auth::guard('pemilih')->check()) {
            return response()->json(['message' => 'Anda harus login terlebih dahulu.'], 401);
        }

        $pemilih = Auth::guard('pemilih')->user();

        $periode = Periode::aktif();

        if (! $periode) {
            return response()->json(['message' => 'Tidak ada periode pemilihan yang sedang berjalan.'], 404);
        }

        // Validasi eksplisit status, bukan cuma andalkan Periode::aktif()
        // (jaga-jaga kalau nanti query aktif() berubah logic-nya).
        if ($periode->status !== 'running') {
            return response()->json(['message' => 'Periode pemilihan tidak sedang berjalan.'], 403);
        }

        // Validasi CUTOFF WAKTU — ini yang menentukan sah/tidaknya suara dari
        // sisi waktu. Dicek terpisah dari status 'running' di atas: kalau jam
        // server sudah lewat waktu_selesai_voting tapi status masih 'running'
        // (misalnya scheduler otomatis belum sempat jalan), suara TETAP ditolak di sini.
        if (! $periode->sedangDalamJangkaWaktu()) {
            return response()->json([
                'message' => 'Waktu pemilihan sudah berakhir. Suara tidak dapat diterima.',
            ], 403);
        }

        // 2. Pastikan pemilih ini benar terdaftar & belum vote di periode aktif.
        // Query manual ke tabel pivot (bukan lewat Eloquent relation) supaya
        // baris pivot bisa langsung dikunci (lockForUpdate) di dalam transaction
        // di bawah, mencegah race condition kalau ada 2 request submit nyaris bersamaan.
        $pivot = DB::table('pemilih_periode')
            ->where('pemilih_id', $pemilih->id)
            ->where('periode_id', $periode->id)
            ->first();

        if (! $pivot) {
            return response()->json(['message' => 'Anda tidak terdaftar sebagai pemilih pada periode ini.'], 403);
        }

        if ($pivot->status_akses === 'sudah_voting') {
            return response()->json(['message' => 'Anda sudah pernah memberikan suara pada periode ini.'], 409);
        }

        if ($pivot->status_akses === 'terkunci') {
            return response()->json(['message' => 'Akun Anda sedang terkunci.'], 403);
        }

        $validated = $request->validate([
            'kandidat_id' => ['required', 'integer', 'exists:kandidats,id'],
        ]);

        // Pastikan kandidat yang dipilih benar-benar milik periode aktif ini,
        // bukan kandidat "nyasar" dari periode tahun lain.
        $kandidat = Kandidat::where('id', $validated['kandidat_id'])
            ->where('periode_id', $periode->id)
            ->first();

        if (! $kandidat) {
            return response()->json(['message' => 'Kandidat tidak ditemukan pada periode ini.'], 422);
        }

        $suara = DB::transaction(function () use ($periode, $kandidat, $pemilih) {
            // Kunci baris pivot pemilih ini supaya request submit ganda yang
            // datang nyaris bersamaan tidak bisa dua-duanya lolos pengecekan
            // "belum voting" di atas sebelum salah satunya sempat commit.
            $pivotLocked = DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $periode->id)
                ->lockForUpdate()
                ->first();

            if ($pivotLocked->status_akses === 'sudah_voting') {
                abort(409, 'Anda sudah pernah memberikan suara pada periode ini.');
            }

            $suara = Suara::create([
                'periode_id' => $periode->id,
                'kandidat_id' => $kandidat->id,
            ]);

            DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $periode->id)
                ->update(['status_akses' => 'sudah_voting', 'updated_at' => now()]);

            // Catat bukti kehadiran (waktu submit saja, TANPA isi pilihan).
            // Tabel ini terpisah total dari tabel suara demi anonimitas.
            AuditLogPresensi::create([
                'pemilih_id' => $pemilih->id,
                'periode_id' => $periode->id,
            ]);

            return $suara;
        });

        // 3. Akses otomatis hangus setelah suara terkirim (sesuai dokumen fitur awal).
        Auth::guard('pemilih')->logout();
        $request->session()->forget('periode_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Suara berhasil dikirim.', 'suara_id' => $suara->id]);
    }

    /**
     * [PUBLIK] Live report turnout untuk landing page.
     * Hanya menampilkan angka turnout, TIDAK menampilkan hasil per kandidat
     * (sesuai fitur: grafik hasil default tersembunyi sampai admin membuka toggle).
     */
    public function liveTurnout()
    {
        $periode = Periode::aktif();

        if (! $periode) {
            return response()->json(['message' => 'Tidak ada periode yang sedang berjalan.'], 404);
        }

        $totalSuaraMasuk = $periode->suaras()->count();
        $totalDpt = $periode->pemilihs()->count();

        return response()->json([
            'periode_id' => $periode->id,
            'total_suara_masuk' => $totalSuaraMasuk,
            'total_dpt' => $totalDpt,
            'turnout_percentage' => $totalDpt ? round(($totalSuaraMasuk / $totalDpt) * 100, 2) : null,
        ]);
    }

    /**
     * [ADMIN] Hasil lengkap per kandidat untuk dashboard admin.
     * TIDAK terpengaruh toggle publik — admin selalu lihat data penuh real-time.
     */
    public function hasilLengkap(Periode $periode)
    {
        $hasil = $periode->kandidats()
            ->withCount('suaras')
            ->orderByDesc('suaras_count')
            ->get(['id', 'nama', 'nomor_urut']);

        $totalSuara = $hasil->sum('suaras_count');

        $hasil = $hasil->map(function ($k) use ($totalSuara) {
            $k->persentase = $totalSuara > 0
                ? round(($k->suaras_count / $totalSuara) * 100, 2)
                : 0;

            return $k;
        });

        return response()->json([
            'periode_id' => $periode->id,
            'status_periode' => $periode->status,
            'total_suara' => $totalSuara,
            'hasil' => $hasil,
        ]);
    }
}
