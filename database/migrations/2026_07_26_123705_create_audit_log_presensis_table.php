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
        // Log presensi: mencatat KAPAN seorang pemilih submit suara,
        // TANPA mencatat kandidat yang dipilih. Ini terpisah total dari
        // tabel `suaras` -- jangan pernah digabung, supaya anonimitas
        // pilihan tetap terjaga sekalipun log presensi ini dibuka admin.
        Schema::create('audit_log_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilih_id')->constrained('pemilihs')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            // Cuma created_at, tanpa updated_at -- log ini append-only,
            // tidak pernah ada aksi "update" terhadap baris yang sudah ada.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log_presensis');
    }
};
