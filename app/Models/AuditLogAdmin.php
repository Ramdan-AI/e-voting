<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLogAdmin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'periode_id',
        'aksi',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    /**
     * Helper singkat buat dipanggil dari controller mana saja:
     *   AuditLogAdmin::catat('ubah_status_periode', 'Periode #1 dari freeze ke running', $periode->id);
     * Otomatis ambil admin yang sedang login dari guard 'admin'.
     * $periodeId opsional -- beberapa aksi admin tidak selalu terkait
     * satu periode tertentu.
     */
    public static function catat(string $aksi, ?string $keterangan = null, ?int $periodeId = null): self
    {
        return static::create([
            'admin_id' => Auth::guard('admin')->id(),
            'periode_id' => $periodeId,
            'aksi' => $aksi,
            'keterangan' => $keterangan,
        ]);
    }
}
