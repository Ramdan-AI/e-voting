<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suara extends Model
{
    protected $fillable = [
        'periode_id',
        'kandidat_id',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kandidat()
    {
        return $this->belongsTo(Kandidat::class, 'kandidat_id');
    }
}
