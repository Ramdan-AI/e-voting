<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanAkun extends Model
{
    protected $table = 'pengaduan_akuns';

    protected $fillable = [
        'identifier',
        'nama',
        'email',
        'no_hp',
        'foto_ktm',
        'keterangan',
        'periode_id',
        'status',
        'catatan_admin',
        'diproses_oleh',
        'diproses_pada',
    ];

    protected $casts = [
        'diproses_pada' => 'datetime',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'diproses_oleh');
    }

    /**
     * Cari baris pivot pemilih_periode milik pengadu ini, dipakai admin
     * untuk cek "dia sudah coblos atau belum" sebelum memutuskan buka kunci.
     *
     * Urutan pencarian (dari yang paling spesifik ke paling longgar),
     * supaya tidak gagal cuma gara-gara periode_id yang tersimpan waktu
     * pengaduan dikirim ternyata kosong/tidak cocok lagi:
     * 1. Periode yang tercatat di pengaduan ini (kalau ada & pivot ketemu).
     * 2. Periode yang sedang aktif (running) saat ini.
     * 3. Pivot manapun milik pemilih ini yang statusnya 'terkunci' (paling baru).
     */
    public function cariPivotPemilih(): ?object
    {
        $pemilih = Pemilih::where('identifier', trim($this->identifier))->first();

        if (! $pemilih) {
            return null;
        }

        if ($this->periode_id) {
            $pivot = \Illuminate\Support\Facades\DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $this->periode_id)
                ->first();

            if ($pivot) {
                return $pivot;
            }
        }

        $periodeAktif = Periode::aktif();

        if ($periodeAktif) {
            $pivot = \Illuminate\Support\Facades\DB::table('pemilih_periode')
                ->where('pemilih_id', $pemilih->id)
                ->where('periode_id', $periodeAktif->id)
                ->first();

            if ($pivot) {
                return $pivot;
            }
        }

        return \Illuminate\Support\Facades\DB::table('pemilih_periode')
            ->where('pemilih_id', $pemilih->id)
            ->where('status_akses', 'terkunci')
            ->latest('updated_at')
            ->first();
    }
}
