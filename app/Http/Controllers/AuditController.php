<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\AuditLogPresensi;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
     * Ikut ditampilkan tipe pemilih (mahasiswa/dosen/staff), diambil dari
     * pivot pemilih_periode -- bisa saja kosong kalau API kampus belum
     * pernah kirim data itu waktu orang tersebut login.
     */
    public function presensi(Request $request, Periode $periode)
    {
        $search = $request->search;

        $presensi = AuditLogPresensi::with('pemilih')
            ->where('periode_id', $periode->id)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('pemilih', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->get();

        $tipePerPemilih = DB::table('pemilih_periode')
            ->where('periode_id', $periode->id)
            ->pluck('tipe_pemilih', 'pemilih_id');

        foreach ($presensi as $log) {
            $log->tipe_pemilih = $tipePerPemilih[$log->pemilih_id] ?? null;
        }

        return view('admin.audit.presensi', compact(
            'periode',
            'presensi',
            'search'
        ));
    }
}
