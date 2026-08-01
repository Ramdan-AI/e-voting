<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pemilih extends Authenticatable
{
    protected $fillable = [
        'identifier',
        'nama',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        // Laravel otomatis hash saat diisi lewat mass-assignment (Laravel 10+).
        'password' => 'hashed',
    ];

    /**
     * Semua periode yang diikuti pemilih ini, lengkap dengan status
     * akses & percobaan gagal per periode (data pivot).
     */
    public function periodes(): BelongsToMany
    {
        return $this->belongsToMany(Periode::class, 'pemilih_periode')
            ->withPivot(['status_akses', 'percobaan_gagal'])
            ->withTimestamps();
    }
}
