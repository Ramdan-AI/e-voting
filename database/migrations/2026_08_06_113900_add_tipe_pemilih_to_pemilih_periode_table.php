<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pemilih_periode', function (Blueprint $table) {
            // Dipasang di PIVOT (bukan di tabel pemilihs), karena secara
            // konsep peran seseorang bisa saja beda antar periode (meski
            // jarang terjadi) -- sesuai keputusan desain jauh di awal yang
            // sempat kelewat belum diimplementasikan. Nullable karena data
            // ini baru terisi kalau API kampus sudah kirim field tipe;
            // pemilih yang sudah ada sebelum kolom ini ditambahkan otomatis
            // NULL, bukan error.
            $table->enum('tipe_pemilih', ['mahasiswa', 'dosen', 'staff'])->nullable()->after('periode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemilih_periode', function (Blueprint $table) {
            $table->dropColumn('tipe_pemilih');
        });
    }
};
