<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogPresensi extends Model
{
    // Cuma created_at yang dipakai (lihat migration), tidak ada updated_at
    // -- log presensi tidak pernah diubah setelah dibuat.
    public $timestamps = false;

    protected $fillable = [
        'pemilih_id',
        'periode_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pemilih(): BelongsTo
    {
        return $this->belongsTo(Pemilih::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }
}
