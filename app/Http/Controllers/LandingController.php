<?php

namespace App\Http\Controllers;

use App\Models\Periode;

class LandingController extends Controller
{
    /**
     * [PUBLIK] Halaman utama.
     */
    public function index()
    {
        $periode = Periode::untukPublik();

        $ringkasan = null;

        if ($periode) {
            $totalSuara = $periode->suaras()->count();
            $totalPemilih = $periode->pemilihs()->count();

            $turnout = $totalPemilih > 0
                ? round(($totalSuara / $totalPemilih) * 100, 2)
                : 0;

            $kandidats = $periode->kandidats()
                ->withCount('suaras')
                ->orderBy('nomor_urut')
                ->get();

            foreach ($kandidats as $kandidat) {
                $kandidat->persentase = $periode->tampilkan_hasil
                    ? (
                        $totalSuara > 0
                        ? round(($kandidat->suaras_count / $totalSuara) * 100, 2)
                        : 0
                    )
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


    /**
     * [PUBLIK] Mengambil hasil suara terbaru untuk landing page.
     *
     * Dipanggil oleh JavaScript secara berkala agar hasil suara
     * dapat berubah tanpa reload halaman.
     */
    public function liveHasil()
    {
        $periode = Periode::untukPublik();

        if (! $periode) {
            return response()->json([
                'tersedia' => false,
                'message' => 'Tidak ada periode pemilihan yang sedang berjalan.',
            ], 404);
        }

        $totalSuara = $periode->suaras()->count();

        $kandidats = $periode->kandidats()
            ->withCount('suaras')
            ->orderBy('nomor_urut')
            ->get();

        $hasil = $kandidats->map(function ($kandidat) use ($totalSuara) {
            $persentase = $totalSuara > 0
                ? round(($kandidat->suaras_count / $totalSuara) * 100, 2)
                : 0;

            return [
                'id' => $kandidat->id,
                'nomor_urut' => $kandidat->nomor_urut,
                'jumlah_suara' => $kandidat->suaras_count,
                'persentase' => $persentase,
            ];
        });

        return response()
            ->json([
                'tersedia' => true,
                'periode_id' => $periode->id,
                'tampilkan_hasil' => (bool) $periode->tampilkan_hasil,
                'total_suara' => $totalSuara,
                'hasil' => $hasil,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
