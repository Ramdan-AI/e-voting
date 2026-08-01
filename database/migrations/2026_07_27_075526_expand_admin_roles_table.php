<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Memperluas enum role dari 3 jadi 7, sesuai struktur kepanitiaan
     * lengkap: Ketua Pelaksana, Sekretaris, Bendahara, Divisi Regulasi &
     * Verifikasi, Divisi Acara & Pengawasan, Divisi Teknis & Pemilihan,
     * Divisi Humas & Media.
     *
     * Pakai raw SQL (bukan Schema::table()->change()) karena mengubah
     * definisi enum di MySQL lewat Laravel butuh package doctrine/dbal
     * tambahan -- raw ALTER TABLE lebih simpel untuk kasus ini.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE admins MODIFY COLUMN role ENUM(
                'ketua_pelaksana',
                'sekretaris',
                'bendahara',
                'divisi_regulasi_verifikasi',
                'divisi_acara_pengawasan',
                'divisi_teknis_pemilihan',
                'divisi_humas_media'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE admins MODIFY COLUMN role ENUM(
                'ketua_pelaksana',
                'koordinator_teknis',
                'koordinator_acara'
            ) NOT NULL
        ");
    }
};
