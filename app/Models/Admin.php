<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * Nama tampilan untuk tiap role, dipakai di UI (badge, dropdown, dsb.)
     * supaya tidak menampilkan slug mentah seperti 'divisi_teknis_pemilihan'.
     */
    public const NAMA_ROLE = [
        'ketua_pelaksana' => 'Ketua Pelaksana',
        'sekretaris' => 'Sekretaris',
        'bendahara' => 'Bendahara',
        'divisi_regulasi_verifikasi' => 'Divisi Regulasi dan Verifikasi',
        'divisi_acara_pengawasan' => 'Divisi Acara dan Pengawasan',
        'divisi_teknis_pemilihan' => 'Divisi Teknis dan Pemilihan',
        'divisi_humas_media' => 'Divisi Humas dan Media',
    ];

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Helper: cek apakah admin ini Ketua Pelaksana.
     * Dipakai untuk aksi yang dibatasi hanya untuk role ini, mis. mengubah
     * status periode ke 'stopped' (aksi paling berisiko, wakil sementara
     * peran superadmin sampai role itu resmi dibuat terpisah).
     */
    public function isKetuaPelaksana(): bool
    {
        return $this->role === 'ketua_pelaksana';
    }

    /**
     * Berikut kumpulan method "bisaX()" -- satu sumber kebenaran untuk hak
     * akses per role, dipakai bareng oleh middleware (EnsureAdminCan) dan
     * view (buat sembunyikan/tampilkan link navigasi). Kalau nanti mapping
     * akses berubah, cukup ubah di sini saja, tidak perlu ubah middleware
     * atau view satu-satu.
     */
    public function bisaKelolaPeriode(): bool
    {
        return in_array($this->role, ['ketua_pelaksana', 'divisi_teknis_pemilihan'], true);
    }

    public function bisaKelolaKandidat(): bool
    {
        return $this->role === 'divisi_regulasi_verifikasi';
    }

    public function bisaKelolaPemilih(): bool
    {
        return in_array($this->role, ['ketua_pelaksana', 'divisi_teknis_pemilihan'], true);
    }

    public function bisaLihatAuditLog(): bool
    {
        return $this->role === 'ketua_pelaksana';
    }

    /**
     * Semua divisi kecuali Ketua Pelaksana wajib isi LPJ untuk divisinya
     * sendiri. Ketua tidak mengisi LPJ -- perannya cuma review & sahkan.
     */
    public function bisaIsiLpj(): bool
    {
        return $this->role !== 'ketua_pelaksana';
    }

    /**
     * Cuma Ketua Pelaksana yang bisa lihat & mengesahkan LPJ milik divisi
     * lain (sesuai keputusan: tidak dibuka untuk saling lihat antar divisi).
     */
    public function bisaReviewLpj(): bool
    {
        return $this->role === 'ketua_pelaksana';
    }

    /**
     * Nama tampilan role admin ini, mis. "Divisi Humas dan Media".
     */
    public function namaRole(): string
    {
        return self::NAMA_ROLE[$this->role] ?? $this->role;
    }
}
