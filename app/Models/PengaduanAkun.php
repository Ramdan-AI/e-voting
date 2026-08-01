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
        'foto_selfie',
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
     * Cari baris pivot pemilih_periode milik pengadu ini (berdasarkan
     * identifier + periode_id), dipakai admin untuk cek "dia sudah
     * coblos atau belum" sebelum memutuskan buka kunci.
     */
    public function cariPivotPemilih(): ?object
    {
        if (! $this->periode_id) {
            return null;
        }

        $pemilih = Pemilih::where('identifier', $this->identifier)->first();

        if (! $pemilih) {
            return null;
        }

        return \Illuminate\Support\Facades\DB::table('pemilih_periode')
            ->where('pemilih_id', $pemilih->id)
            ->where('periode_id', $this->periode_id)
            ->first();
    }
}
