<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * [PUBLIK] Tampilkan form login (landing page).
     */
    public function showLoginForm()
    {
        return view('auth.login-pemilih');
    }

    /**
     * [PUBLIK] Login pemilih untuk periode yang sedang aktif.
     *
     * CATATAN PENTING: versi ini pakai identifier + password LOKAL
     * (Hash::check biasa) — KHUSUS untuk testing lokal, sesuai permintaan.
     * Begitu integrasi API kampus / mekanisme OTP sudah diputuskan tim IT,
     * yang perlu diganti CUKUP bagian validasi password di bawah (langkah
     * ke-5). Struktur pivot, rate limiting, dan alur voting setelahnya
     * tidak perlu dibongkar ulang.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 1. Harus ada periode yang sedang berjalan.
        $periode = Periode::aktif();

        if (! $periode) {
            return back()->withErrors(['identifier' => 'Tidak ada periode pemilihan yang sedang berjalan saat ini.']);
        }

        // 2. Identifier harus terdaftar di master data pemilih.
        $pemilih = Pemilih::where('identifier', $validated['identifier'])->first();

        if (! $pemilih) {
            // Pesan spesifik sesuai dokumen fitur awal, supaya pemilih tahu
            // harus lapor lewat Google Form bantuan dengan kategori yang tepat.
            return back()->withErrors(['identifier' => 'NIM/identifier tidak ditemukan dalam sistem.']);
        }

        // 3. Harus terdaftar sebagai pemilih pada periode aktif ini (DPT).
        $pivot = DB::table('pemilih_periode')
            ->where('pemilih_id', $pemilih->id)
            ->where('periode_id', $periode->id)
            ->first();

        if (! $pivot) {
            return back()->withErrors(['identifier' => 'Anda tidak terdaftar sebagai pemilih pada periode ini.']);
        }

        // 4. Cek status akses: terkunci atau sudah pernah vote.
        if ($pivot->status_akses === 'terkunci') {
            return back()->withErrors(['identifier' => 'Akun terkunci karena terlalu banyak percobaan gagal. Silakan hubungi panitia lewat form bantuan.']);
        }

        if ($pivot->status_akses === 'sudah_voting') {
            return back()->withErrors(['identifier' => 'NIM ini sudah pernah digunakan untuk memberikan suara pada periode ini.']);
        }

        // 5. Validasi password. INI bagian yang akan diganti begitu
        // integrasi API kampus / OTP final.
        if (! Hash::check($validated['password'], $pemilih->password)) {
            $percobaanBaru = $pivot->percobaan_gagal + 1;
            $statusBaru = $percobaanBaru >= 5 ? 'terkunci' : $pivot->status_akses;

            DB::table('pemilih_periode')
                ->where('id', $pivot->id)
                ->update([
                    'percobaan_gagal' => $percobaanBaru,
                    'status_akses' => $statusBaru,
                    'updated_at' => now(),
                ]);

            if ($statusBaru === 'terkunci') {
                return back()->withErrors(['identifier' => 'Password salah 5 kali. Akun terkunci, silakan hubungi panitia lewat form bantuan.']);
            }

            $sisaPercobaan = 5 - $percobaanBaru;

            return back()->withErrors(['identifier' => "Password salah. Sisa percobaan: {$sisaPercobaan}."]);
        }

        // 6. Login berhasil: reset percobaan gagal, simpan konteks periode di session.
        DB::table('pemilih_periode')
            ->where('id', $pivot->id)
            ->update(['percobaan_gagal' => 0, 'updated_at' => now()]);

        Auth::guard('pemilih')->login($pemilih);
        $request->session()->put('periode_id', $periode->id);
        $request->session()->regenerate();

        return redirect()->route('voting.index');
    }

    /**
     * [PEMILIH] Logout manual.
     * Sesi normalnya otomatis hangus setelah submit suara (lihat
     * SuaraController::store()), tapi ini tetap disediakan untuk kasus
     * pemilih batal/keluar di tengah jalan sebelum submit.
     */
    public function logout(Request $request)
    {
        Auth::guard('pemilih')->logout();
        $request->session()->forget('periode_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
