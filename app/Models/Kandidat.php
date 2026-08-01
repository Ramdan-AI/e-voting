<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kandidat extends Model
{
    protected $fillable = [
        'periode_id',
        'nama',
        'nomor_urut',
        'visi',
        'misi',
        'foto',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function suaras()
    {
        return $this->hasMany(Suara::class, 'kandidat_id');
    }

    public function totalSuara()
    {
        return $this->suaras()->count();
    }
}
