<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Models\Periode;
use App\Services\CampusAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

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
     * Alur BARU (menggantikan versi password-lokal + OTP sebelumnya, sesuai
     * hasil diskusi dengan tim IT kampus):
     * 1. Pemilih login pakai email + password + tanggal lahir.
     * 2. Kredensial itu divalidasi LANGSUNG ke API kampus lewat
     *    CampusAuthService -- sistem KPUM tidak pernah menyimpan/validasi
     *    password sendiri lagi.
     * 3. Begitu API konfirmasi valid, sistem AUTO-PROVISION data pemilih
     *    lokal (bikin baris `pemilih` + `pemilih_periode` kalau belum ada)
     *    -- ini menggantikan kebutuhan Import DPT manual, karena DPT
     *    "terbentuk sendiri" begitu orang yang berhak login.
     * 4. TIDAK ADA lagi tahap OTP -- tanggal lahir yang sekarang berfungsi
     *    sebagai faktor tambahan (mirip captcha identitas), makanya OTP
     *    dianggap tidak perlu lagi oleh tim IT.
     *
     * CATATAN SOAL RATE LIMITING: sebelum orang berhasil login minimal
     * sekali, sistem BELUM punya baris `pemilih` lokal untuk dia -- jadi
     * penguncian 3x percobaan gagal di tahap ini pakai RateLimiter bawaan
     * Laravel (kunci sementara 15 menit, keyed by email), BUKAN status
     * 'terkunci' di tabel pivot seperti sebelumnya. Konsekuensinya: orang
     * yang belum PERNAH berhasil login sama sekali tidak akan muncul di
     * halaman admin "Akun Terkunci" kalau dia salah 3x -- dia cuma perlu
     * nunggu 15 menit, atau admin bisa buka blokirnya manual by email
     * (lihat PengaduanController::bukaBlokirEmail()).
     * Begitu pemilih PERNAH sukses login sekali, penguncian selanjutnya
     * kembali pakai mekanisme pivot `pemilih_periode` yang sudah ada
     * (percobaan_gagal, status_akses) -- sama seperti sebelumnya.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d'],
        ]);

        $periode = Periode::aktif();

        if (! $periode) {
            return back()->withErrors(['email' => 'Tidak ada periode pemilihan yang sedang berjalan saat ini.']);
        }

        $throttleKey = 'login-pemilih:' . strtolower($validated['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $detik = RateLimiter::availableIn($throttleKey);
            $menit = ceil($detik / 60);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit, atau hubungi panitia lewat form bantuan.",
            ]);
        }

        try {
            $hasil = app(CampusAuthService::class)->verify(
                $validated['email'],
                $validated['password'],
                $validated['tanggal_lahir']
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Tidak bisa menghubungi server kampus saat ini. Coba lagi sebentar lagi.']);
        }

        if (! $hasil['valid']) {
            RateLimiter::hit($throttleKey, 900); // kunci 15 menit kalau sudah 3x

            $sisa = 3 - RateLimiter::attempts($throttleKey);

            $pesan = $hasil['message'] ?? 'Email, password, atau tanggal lahir tidak cocok.';

            if ($sisa > 0) {
                $pesan .= " Sisa percobaan: {$sisa}.";
            }

            return back()->withErrors(['email' => $pesan]);
        }

        RateLimiter::clear($throttleKey);

        if (! $hasil['nim']) {
            return back()->withErrors(['email' => 'Data NIM tidak ditemukan pada respons server kampus. Hubungi panitia lewat form bantuan.']);
        }

        // Auto-provision: bikin baris pemilih lokal kalau belum ada.
        // Menggantikan Import DPT manual -- DPT "terbentuk sendiri" dari
        // orang yang memang berhak login lewat API kampus.
        $pemilih = Pemilih::firstOrNew(['identifier' => $hasil['nim']]);

        $namaDariApi = $hasil['nama'] ?? null;

        if (! $pemilih->exists) {
            // Kalau API belum kirim 'nama' (belum dikonfirmasi tim IT),
            // pakai NIM sebagai placeholder sementara -- tidak error,
            // cuma tampilannya kurang bagus sampai field itu tersedia.
            $pemilih->nama = $namaDariApi ?: $hasil['nim'];
        } elseif ($namaDariApi && $pemilih->nama === $pemilih->identifier) {
            // "Self-healing": kalau sebelumnya sempat kepaksa pakai NIM
            // sebagai placeholder nama, begitu API mulai kirim nama asli,
            // otomatis diperbaiki di sini -- tidak perlu perbaikan data manual.
            $pemilih->nama = $namaDariApi;
        }

        $pemilih->email = $hasil['email'];
        $pemilih->save();

        // Auto-daftarkan ke periode aktif ini kalau belum terdaftar.
        $pivot = DB::table('pemilih_periode')
            ->where('pemilih_id', $pemilih->id)
            ->where('periode_id', $periode->id)
            ->first();

        if (! $pivot) {
            DB::table('pemilih_periode')->insert([
                'pemilih_id' => $pemilih->id,
                'periode_id' => $periode->id,
                'status_akses' => 'belum_voting',
                'percobaan_gagal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $pivot = DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $periode->id)
                ->first();
        }

        if ($pivot->status_akses === 'terkunci') {
            return back()->withErrors(['email' => 'Akun terkunci. Silakan hubungi panitia lewat form bantuan.']);
        }

        if ($pivot->status_akses === 'sudah_voting') {
            return back()->withErrors(['email' => 'Akun ini sudah pernah digunakan untuk memberikan suara pada periode ini.']);
        }

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
