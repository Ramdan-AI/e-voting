<?php

namespace App\Http\Controllers;

use App\Models\AuditLogAdmin;
use App\Models\Periode;
use App\Models\Pemilih;
use Illuminate\Support\Facades\DB;

class PemilihController extends Controller
{
    /**
     * [ADMIN] Daftar pemilih yang statusnya terkunci pada suatu periode
     * (gagal login 3x). Ini yang jadi acuan admin waktu ada laporan masuk
     * lewat Google Form bantuan ("akun kena rate limit").
     */
    public function terkunci(Periode $periode)
    {
        $terkunci = $periode->pemilihs()
            ->wherePivot('status_akses', 'terkunci')
            ->get();

        return view('admin.pemilih.terkunci', compact('periode', 'terkunci'));
    }

    /**
     * [ADMIN] Buka kunci satu pemilih untuk periode tertentu.
     * Reset percobaan_gagal ke 0 dan status_akses kembali ke belum_voting,
     * supaya pemilih bisa mencoba login lagi.
     */
    public function unlock(Periode $periode, Pemilih $pemilih)
    {
        $updated = DB::table('pemilih_periode')
            ->where('periode_id', $periode->id)
            ->where('pemilih_id', $pemilih->id)
            ->where('status_akses', 'terkunci') // guard: cuma efek kalau memang lagi terkunci
            ->update([
                'status_akses' => 'belum_voting',
                'percobaan_gagal' => 0,
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Akun ini tidak sedang dalam status terkunci.');
        }

        AuditLogAdmin::catat(
            'unlock_akun',
            "Membuka kunci akun pemilih {$pemilih->identifier} ({$pemilih->nama}) pada periode #{$periode->id} ({$periode->judul}).",
            $periode->id
        );

        return back()->with('success', "Akun {$pemilih->identifier} berhasil dibuka kembali.");
    }
}
