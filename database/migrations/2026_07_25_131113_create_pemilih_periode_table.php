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
        // Tabel penghubung many-to-many antara pemilih dan periode.
        // Status akses, percobaan gagal, dsb. adalah kondisi PER PERIODE,
        // bukan melekat permanen ke satu orang -- makanya bukan kolom di
        // tabel `pemilihs`, tapi di sini.
        Schema::create('pemilih_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilih_id')->constrained('pemilihs')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->enum('status_akses', ['belum_voting', 'sudah_voting', 'terkunci'])->default('belum_voting');
            $table->unsignedTinyInteger('percobaan_gagal')->default(0);
            $table->timestamps();

            // Satu pemilih hanya boleh punya satu baris per periode.
            $table->unique(['pemilih_id', 'periode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilih_periode');
    }
};
