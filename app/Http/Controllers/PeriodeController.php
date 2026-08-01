<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PeriodeController extends Controller
{
    /**
     * [ADMIN] Daftar semua periode (untuk dashboard admin/superadmin).
     */
    public function index()
    {
        $periodes = Periode::latest('id')->paginate(10);

        return view('admin.periode.index', compact('periodes'));
    }

    /**
     * [ADMIN] Form buat periode baru.
     */
    public function create()
    {
        return view('admin.periode.create');
    }

    /**
     * [ADMIN] Simpan periode baru.
     * start_date/end_date di sini adalah rentang KESELURUHAN acara KPUM
     * (rapat pertama panitia s.d. acara selesai), BUKAN jendela pencoblosan.
     * Jendela pencoblosan diset belakangan lewat setJadwalVoting(), biasanya
     * baru diketahui pasti mendekati hari-H.
     * Default status selalu 'freeze' dulu saat dibuat, supaya admin sadar
     * betul kapan sebuah periode benar-benar dibuka ke publik.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $periode = Periode::create([
            ...$validated,
            'status' => 'freeze', // dibuat dalam kondisi terkunci dulu, admin yang buka manual
        ]);

        AuditLogAdmin::catat('buat_periode', "Membuat periode baru: {$periode->judul} (#{$periode->id}).", $periode->id);

        return redirect()
            ->route('admin.periode.index')
            ->with('success', "Periode #{$periode->id} berhasil dibuat dengan status freeze.");
    }

    /**
     * [ADMIN] Set/ubah jendela waktu pencoblosan (waktu_mulai_voting s.d.
     * waktu_selesai_voting) untuk suatu periode. Ini yang dipakai sistem
     * untuk validasi sah/tidaknya suara dari sisi waktu — terpisah dari
     * start_date/end_date yang menandai rentang keseluruhan acara.
     *
     * DIKUNCI: hanya bisa diubah selama status periode masih 'freeze' DAN
     * belum ada satu pun suara masuk. Begitu periode pernah dibuka 'running'
     * atau sudah ada suara tercatat, jadwal tidak boleh diubah lagi — supaya
     * jendela waktu tidak berubah di tengah/usai proses pencoblosan, yang
     * bisa merusak validitas suara yang sudah masuk sebelumnya.
     */
    public function setJadwalVoting(Request $request, Periode $periode)
    {
        if ($periode->status !== 'freeze') {
            return back()->with('error', 'Jadwal pencoblosan hanya bisa diatur selama status periode masih freeze.');
        }

        if ($periode->suaras()->exists()) {
            return back()->with('error', 'Jadwal pencoblosan tidak bisa diubah karena sudah ada suara yang masuk pada periode ini.');
        }

        $validated = $request->validate([
            'waktu_mulai_voting' => ['required', 'date'],
            'waktu_selesai_voting' => ['required', 'date', 'after:waktu_mulai_voting'],
        ]);

        $periode->update($validated);

        AuditLogAdmin::catat(
            'set_jadwal_voting',
            "Mengatur jadwal pencoblosan periode #{$periode->id} ({$periode->judul}): {$validated['waktu_mulai_voting']} s.d. {$validated['waktu_selesai_voting']}.",
            $periode->id
        );

        return back()->with('success', "Jadwal pencoblosan periode #{$periode->id} berhasil diatur.");
    }

    /**
     * [ADMIN] Ubah status periode: running / freeze / stopped.
     *
     * Pembatasan role untuk status 'stopped': untuk sementara dibatasi ke
     * role 'ketua_pelaksana' (sesuai dokumen fitur awal: "Reset Periode
     * Pemilihan Khusus Ketua Pelaksana"). Begitu role superadmin resmi
     * dibuat terpisah dari 3 akun panitia ini, pembatasan ini kemungkinan
     * perlu dipindah ke guard/role superadmin, bukan ketua_pelaksana lagi.
     */
    public function updateStatus(Request $request, Periode $periode)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['running', 'freeze', 'stopped'])],
        ]);

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if ($validated['status'] === 'stopped' && ! $admin->isKetuaPelaksana()) {
            return back()->with('error', 'Hanya Ketua Pelaksana yang dapat menghentikan (stopped) periode ini.');
        }

        // Cegah kesalahan umum: status tidak boleh diubah ke 'running' kalau
        // jendela pencoblosan belum diset, karena sedangDalamJangkaWaktu()
        // akan selalu menolak suara tanpa jendela waktu yang jelas.
        if (
            $validated['status'] === 'running'
            && (! $periode->waktu_mulai_voting || ! $periode->waktu_selesai_voting)
        ) {
            return back()->with('error', 'Set jadwal pencoblosan (jam mulai & selesai) terlebih dahulu sebelum membuka status running.');
        }

        $statusLama = $periode->status;

        // Hanya boleh ada satu periode berstatus 'running' pada satu waktu.
        if ($validated['status'] === 'running') {
            Periode::where('status', 'running')
                ->where('id', '!=', $periode->id)
                ->update(['status' => 'freeze']);
        }

        $periode->update(['status' => $validated['status']]);

        AuditLogAdmin::catat(
            'ubah_status_periode',
            "Periode #{$periode->id} ({$periode->judul}) diubah dari '{$statusLama}' ke '{$validated['status']}'.",
            $periode->id
        );

        return back()->with('success', "Status periode #{$periode->id} diubah ke {$validated['status']}.");
    }

    /**
     * [ADMIN] Buka/tutup toggle grafik hasil di landing page publik.
     * Default tersembunyi (lihat migration tampilkan_hasil), admin yang
     * membuka secara manual.
     */
    public function toggleHasil(Periode $periode)
    {
        $periode->update(['tampilkan_hasil' => ! $periode->tampilkan_hasil]);

        $status = $periode->tampilkan_hasil ? 'ditampilkan' : 'disembunyikan';

        AuditLogAdmin::catat(
            'toggle_hasil',
            "Grafik hasil periode #{$periode->id} ({$periode->judul}) diubah menjadi {$status}.",
            $periode->id
        );

        return back()->with('success', "Grafik hasil untuk periode #{$periode->id} sekarang {$status} di landing page.");
    }

    /**
     * [PUBLIK] Dipakai landing page untuk tahu periode mana yang sedang aktif.
     */
    public function aktif()
    {
        $periode = Periode::aktif();

        if (! $periode) {
            return response()->json(['message' => 'Tidak ada periode yang sedang berjalan saat ini.'], 404);
        }

        return response()->json([
            'id' => $periode->id,
            'judul' => $periode->judul,
            'status' => $periode->status,
            'waktu_mulai_voting' => $periode->waktu_mulai_voting,
            'waktu_selesai_voting' => $periode->waktu_selesai_voting,
        ]);
    }
}
