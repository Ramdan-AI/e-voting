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
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100);
            $table->enum('status', ['running', 'freeze', 'stopped'])->default('freeze');

            // Rentang KESELURUHAN acara KPUM: dari hari pertama rapat panitia
            // sampai acara selesai (bisa berminggu-minggu/berbulan-bulan).
            // Dipakai untuk konteks/pelaporan, BUKAN untuk validasi sah/tidaknya suara.
            $table->dateTime('start_date');
            $table->dateTime('end_date');

            // Jendela PENCOBLOSAN spesifik, mis. 22 Juli 2026 07:00 s.d. 18:00.
            // Field inilah yang dipakai untuk validasi cutoff waktu suara sah/tidak,
            // BUKAN start_date/end_date di atas.
            $table->dateTime('waktu_mulai_voting')->nullable();
            $table->dateTime('waktu_selesai_voting')->nullable();

            $table->timestamps();

            // Catatan: kolom `date` dari migration sebelumnya dihapus karena
            // isinya sudah tercakup di start_date/end_date. Tambahkan kembali
            // kalau ternyata kolom ini masih dibutuhkan untuk keperluan lain.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodes');
    }
};
