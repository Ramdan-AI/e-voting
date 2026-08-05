<?php

namespace App\Http\Middleware;

use App\Models\Periode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockAdminSaatStopped
{
    /**
     * Terapkan aturan "stopped = cuma superadmin" bukan cuma di titik login,
     * tapi di SETIAP request admin -- supaya admin yang sudah login sebelum
     * status berubah jadi stopped tetap otomatis ter-logout begitu dia
     * membuka halaman apapun setelah statusnya berubah.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && ! $admin->bisaAksesSaatStopped()) {
            $periodeTerbaru = Periode::latest('id')->first();

            if ($periodeTerbaru && $periodeTerbaru->status === 'stopped') {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Sesi Anda diakhiri karena periode sekarang berstatus stopped. Hanya superadmin yang dapat mengakses.',
                ]);
            }
        }

        return $next($request);
    }
}
