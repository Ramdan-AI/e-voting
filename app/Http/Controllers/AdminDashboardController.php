<?php

namespace App\Http\Controllers;

use App\Models\Periode;

class AdminDashboardController extends Controller
{
    /**
     * [ADMIN] Ringkasan singkat: periode aktif, total suara masuk,
     * total pemilih terdaftar, status periode. Ini yang pertama dilihat
     * admin setelah login.
     */
    public function index()
    {
        $periodeAktif = Periode::aktif();
        $semuaPeriode = Periode::latest('id')->get(['id', 'judul', 'status']);

        $ringkasan = null;

        if ($periodeAktif) {
            $ringkasan = [
                'periode' => $periodeAktif,
                'total_suara' => $periodeAktif->suaras()->count(),
                'total_pemilih' => $periodeAktif->pemilihs()->count(),
                'total_kandidat' => $periodeAktif->kandidats()->count(),
            ];
        }

        return view('admin.dashboard', compact('ringkasan', 'semuaPeriode'));
    }
}
