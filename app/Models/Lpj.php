<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lpj extends Model
{
    protected $fillable = [
        'periode_id',
        'divisi',
        'pj_nama',
        'anggota_divisi',
        'ringkasan',
        'tugas_pokok_fungsi',
        'parameter_keberhasilan',
        'kritik',
        'saran',
        'faktor_pendukung',
        'faktor_penghambat',
        'evaluasi',
        'rincian_anggaran',
        'file_wajib',
        'file_opsional',
        'status',
        'catatan_ketua',
        'disahkan_oleh',
        'diajukan_pada',
        'disahkan_pada',
    ];

    protected $casts = [
        'diajukan_pada' => 'datetime',
        'disahkan_pada' => 'datetime',
    ];

    // Nama tampilan divisi (dipakai di view supaya tidak nampilin slug mentah).
    public const NAMA_DIVISI = [
        'sekretaris' => 'Sekretaris',
        'bendahara' => 'Bendahara',
        'divisi_regulasi_verifikasi' => 'Divisi Regulasi & Verifikasi',
        'divisi_acara_pengawasan' => 'Divisi Acara & Pengawasan',
        'divisi_teknis_pemilihan' => 'Divisi Teknis & Pemilihan',
        'divisi_humas_media' => 'Divisi Humas & Media',
    ];

    public function namaDivisi(): string
    {
        return self::NAMA_DIVISI[$this->divisi] ?? $this->divisi;
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function disahkanOleh(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'disahkan_oleh');
    }

    public function isEditable(): bool
    {
        // Bisa diedit PJ divisi selama belum disahkan. Status 'direvisi'
        // sengaja tetap termasuk bisa diedit -- itu justru maksudnya
        // "silakan revisi lagi".
        return $this->status !== 'disahkan';
    }
}
