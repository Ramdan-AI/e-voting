<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pemilih extends Authenticatable
{
    protected $fillable = [
        'identifier',
        'nama',
        'email',
    ];

    public function periodes(): BelongsToMany
    {
        return $this->belongsToMany(Periode::class, 'pemilih_periode')
            ->withPivot(['status_akses', 'percobaan_gagal'])
            ->withTimestamps();
    }
}
