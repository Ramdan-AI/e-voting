<?php

namespace App\Http\Controllers;

use App\Models\Periode;

class LandingController extends Controller
{
    /**
     * [PUBLIK] Halaman utama tanpa login. Menampilkan turnout real-time,
     * kartu profil tiap kandidat (foto, nomor urut, visi-misi -- SELALU
     * tampil, ini profil publik biasa), dan persentase suara PER kandidat
     * (cuma tampil kalau toggle admin dibuka -- tetap menjaga fitur
     * "sembunyikan hasil saat voting berlangsung"). Form login pemilih
     * TIDAK di sini lagi -- dipindah ke navbar (lihat landing.blade.php).
     * TIDAK ada link ke login admin/superadmin -- sengaja dipisah, cuma
     * diakses lewat URL terpisah yang diketahui panitia.
     */
    public function index()
    {
        $periode = Periode::untukPublik();

        $ringkasan = null;

        if ($periode) {
            $totalSuara = $periode->suaras()->count();
            $totalPemilih = $periode->pemilihs()->count();
            $turnout = $totalPemilih > 0 ? round(($totalSuara / $totalPemilih) * 100, 2) : 0;

            // Profil kandidat SELALU diambil (foto, visi-misi bukan rahasia).
            $kandidats = $periode->kandidats()
                ->withCount('suaras')
                ->orderBy('nomor_urut')
                ->get();

            foreach ($kandidats as $kandidat) {
                // persentase cuma dihitung & ditampilkan kalau toggle admin
                // dibuka; kalau belum, biarkan null supaya view tahu harus
                // sembunyikan angkanya (bukan tampilkan 0%, itu beda makna).
                $kandidat->persentase = $periode->tampilkan_hasil
                    ? ($totalSuara > 0 ? round(($kandidat->suaras_count / $totalSuara) * 100, 2) : 0)
                    : null;
            }

            $ringkasan = [
                'total_suara' => $totalSuara,
                'total_pemilih' => $totalPemilih,
                'turnout' => $turnout,
                'tampilkan_hasil' => $periode->tampilkan_hasil,
                'kandidats' => $kandidats,
            ];
        }

        return view('landing', compact('periode', 'ringkasan'));
    }
}
