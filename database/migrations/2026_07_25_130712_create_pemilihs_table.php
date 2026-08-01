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
        Schema::create('pemilihs', function (Blueprint $table) {
            $table->id();
            // identifier = NIM (mahasiswa) / NIP / ID Kepegawaian (dosen-staff).
            // Ini master data identitas, TIDAK terikat ke satu periode saja,
            // karena satu orang bisa ikut KPUM di lebih dari satu tahun.
            $table->string('identifier', 30)->unique();
            $table->string('nama', 100);
            // Password lokal untuk keperluan TESTING saja. Begitu integrasi
            // API kampus / mekanisme OTP final, validasi login akan diganti
            // di AuthController, kolom ini kemungkinan besar tidak lagi jadi
            // sumber kebenaran (lihat catatan di AuthController).
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilihs');
    }
};
