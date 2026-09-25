<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Models\Periode;
use App\Services\CampusAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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
            report($e);

            return back()->withErrors([
                'email' => 'Gagal menghubungi server verifikasi kampus. Silakan coba lagi beberapa saat lagi.',
            ]);
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

        if (!$hasil['id']) {
            return back()->withErrors(['email' => 'Data NIM tidak ditemukan pada respons server kampus. Hubungi panitia lewat form bantuan.']);
        }
        $pemilih = Pemilih::firstOrNew(['identifier' => (string) $hasil['id']]);

        $namaDariApi = $hasil['nama'] ?? null;

        if (! $pemilih->exists) {

            $pemilih->nama = $namaDariApi ?: $hasil['email'];
            $pemilih->email = $hasil['email'];

            // Password dummy (tidak dipakai untuk login)
            $pemilih->password = Hash::make(str()->random(40));
        } else {

            if ($namaDariApi && $pemilih->nama === $pemilih->email) {
                $pemilih->nama = $namaDariApi;
            }

            $pemilih->email = $hasil['email'];
        }

        $pemilih->save();

        $tipeDariApi = $hasil['tipe'] ?? null;

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
                'tipe_pemilih' => $tipeDariApi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $pivot = DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $periode->id)
                ->first();
        } elseif ($tipeDariApi && ! $pivot->tipe_pemilih) {
            // Self-healing sama seperti nama: begitu API mulai kirim tipe,
            // data lama yang sempat kosong otomatis terisi tanpa perbaikan manual.
            DB::table('pemilih_periode')->where('id', $pivot->id)->update(['tipe_pemilih' => $tipeDariApi]);
            $pivot->tipe_pemilih = $tipeDariApi;
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
