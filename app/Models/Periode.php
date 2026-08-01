<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    protected $fillable = [
        'judul',
        'status',
        'tampilkan_hasil',
        'start_date',
        'end_date',
        'waktu_mulai_voting',
        'waktu_selesai_voting',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'waktu_mulai_voting' => 'datetime',
        'waktu_selesai_voting' => 'datetime',
        'tampilkan_hasil' => 'boolean',
    ];

    /**
     * Semua pemilih yang terdaftar di periode ini, lengkap dengan status
     * akses & percobaan gagal per pemilih (data pivot).
     */
    public function pemilihs(): BelongsToMany
    {
        return $this->belongsToMany(Pemilih::class, 'pemilih_periode')
            ->withPivot(['status_akses', 'percobaan_gagal'])
            ->withTimestamps();
    }

    /**
     * Satu periode punya banyak kandidat.
     */
    public function kandidats(): HasMany
    {
        return $this->hasMany(Kandidat::class);
    }

    /**
     * Satu periode punya banyak suara masuk.
     */
    public function suaras(): HasMany
    {
        return $this->hasMany(Suara::class);
    }

    /**
     * Helper: cek apakah periode ini sedang berjalan (pemilih boleh vote).
     * Ini status yang diatur admin (manual atau otomatis lewat scheduler).
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Helper: cek apakah waktu SEKARANG masih berada di dalam JENDELA
     * PENCOBLOSAN (waktu_mulai_voting s.d. waktu_selesai_voting) — BUKAN
     * rentang keseluruhan acara (start_date/end_date), karena keduanya
     * punya skala waktu yang jauh berbeda (jendela voting cuma beberapa
     * jam, rentang acara bisa berminggu-minggu).
     *
     * Ini pengecekan TERPISAH dari status 'running'. Alasannya: status bisa
     * saja masih 'running' walau jendela waktu sudah lewat (misal scheduler
     * yang harusnya otomatis ubah status ke freeze gagal jalan). Suara yang
     * masuk setelah waktu_selesai_voting tetap harus ditolak walau status
     * belum sempat berubah, supaya validitas suara tidak bergantung 100%
     * pada status manual.
     */
    public function sedangDalamJangkaWaktu(): bool
    {
        if (! $this->waktu_mulai_voting || ! $this->waktu_selesai_voting) {
            // Jendela voting belum diset oleh admin -> anggap belum boleh menerima suara.
            return false;
        }

        $sekarang = now();

        return $sekarang->greaterThanOrEqualTo($this->waktu_mulai_voting)
            && $sekarang->lessThanOrEqualTo($this->waktu_selesai_voting);
    }

    /**
     * Helper: ambil satu-satunya periode yang statusnya running saat ini.
     * Berguna karena landing page & voting selalu merujuk ke periode aktif.
     */
    public static function aktif(): ?self
    {
        return static::where('status', 'running')->latest('id')->first();
    }

    /**
     * Helper: ambil periode yang boleh ditampilkan di landing page publik.
     * Mencakup status 'running' (masih pencoblosan) DAN 'freeze' (voting
     * ditutup tapi hasil masih boleh dilihat publik kalau toggle dibuka).
     * Status 'stopped' SENGAJA tidak termasuk -- itu arsip, publik tidak
     * boleh akses sama sekali (sesuai matriks akses yang sudah disepakati).
     */
    public static function untukPublik(): ?self
    {
        return static::whereIn('status', ['running', 'freeze'])->latest('id')->first();
    }
}
