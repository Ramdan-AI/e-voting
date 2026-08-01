<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\AuditLogPresensi;
use App\Models\Periode;

class AuditController extends Controller
{
    /**
     * [ADMIN] Daftar aksi admin terbaru (siapa, kapan, aksi apa).
     * Lintas periode -- ini log global, bukan per periode.
     */
    public function index()
    {
        $logs = AuditLogAdmin::with(['admin', 'periode'])
            ->latest('id')
            ->paginate(20);

        return view('admin.audit.index', compact('logs'));
    }

    /**
     * [ADMIN] Log presensi (waktu submit suara, TANPA isi pilihan) untuk
     * satu periode tertentu. Dipakai untuk verifikasi jumlah suara masuk
     * cocok dengan jumlah presensi -- bukan untuk melihat siapa memilih apa.
     */
    public function presensi(Periode $periode)
    {
        $presensi = AuditLogPresensi::with('pemilih')
            ->where('periode_id', $periode->id)
            ->latest('created_at')
            ->get();

        return view('admin.audit.presensi', compact('periode', 'presensi'));
    }
}
