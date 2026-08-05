<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * [PUBLIK] Tampilkan form login admin.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * [PUBLIK] Proses login admin.
     * Pakai guard terpisah ('admin') dari guard pemilih, supaya sesi
     * admin dan sesi pemilih tidak pernah bentrok/tertukar walau
     * dibuka di browser yang sama.
     *
     * Kalau periode terbaru berstatus 'stopped', SEMUA admin diblokir
     * login kecuali superadmin -- ini yang menegakkan aturan "stopped =
     * cuma superadmin yang bisa akses" di titik paling awal (login),
     * bukan cuma dibatasi per halaman.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($validated)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        $periodeTerbaru = Periode::latest('id')->first();

        if ($periodeTerbaru && $periodeTerbaru->status === 'stopped' && ! $admin->bisaAksesSaatStopped()) {
            Auth::guard('admin')->logout();

            return back()->withErrors([
                'email' => 'Periode sedang berstatus stopped (diarsipkan). Hanya superadmin yang dapat login saat ini.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    /**
     * [ADMIN] Logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
