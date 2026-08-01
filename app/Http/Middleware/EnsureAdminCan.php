<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCan
{
    /**
     * Gate route admin berdasarkan method "bisaX()" di model Admin, mis.:
     *   Route::middleware('admin.can:bisaKelolaPeriode')->group(...);
     *
     * Satu sumber kebenaran ada di model Admin (method bisaKelolaPeriode,
     * bisaKelolaKandidat, dst.) -- middleware ini cuma pemanggil generik,
     * supaya kalau mapping akses berubah, cukup ubah di model saja.
     */
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! method_exists($admin, $capability) || ! $admin->{$capability}()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
