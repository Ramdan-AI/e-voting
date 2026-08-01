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
        Schema::create('pengaduan_akuns', function (Blueprint $table) {
            $table->id();
            // Nullable & tidak pakai foreign key ke pemilihs -- sengaja, karena
            // pengadu bisa jadi salah ketik identifier atau memang tidak
            // terdaftar sama sekali (itu sendiri informasi penting buat admin).
            $table->string('identifier', 30);
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('foto_selfie'); // bukti identitas, wajib
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('set null');

            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('diproses_pada')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan_akuns');
    }
};