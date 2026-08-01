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
        Schema::create('lpjs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');

            // Divisi yang mengisi -- 6 role selain ketua_pelaksana, karena
            // Ketua tidak mengisi LPJ sendiri, cuma review & sahkan punya
            // divisi lain.
            $table->enum('divisi', [
                'sekretaris',
                'bendahara',
                'divisi_regulasi_verifikasi',
                'divisi_acara_pengawasan',
                'divisi_teknis_pemilihan',
                'divisi_humas_media',
            ]);

            // --- Cover ---
            $table->string('pj_nama', 100);
            $table->text('anggota_divisi')->nullable(); // daftar nama, teks bebas

            // --- Isi laporan, sesuai format yang diberikan ---
            $table->text('ringkasan')->nullable();
            $table->text('tugas_pokok_fungsi')->nullable();
            $table->text('parameter_keberhasilan')->nullable();
            $table->text('kritik')->nullable();
            $table->text('saran')->nullable();
            $table->text('faktor_pendukung')->nullable();
            $table->text('faktor_penghambat')->nullable();
            $table->text('evaluasi')->nullable();

            // Laporan anggaran -- opsional, cuma diisi kalau divisi itu ada
            // pengeluaran. bukti_anggaran = path file upload nota (boleh 1
            // file gabungan; kalau perlu banyak file terpisah, dikembangkan
            // belakangan sebagai tabel tersendiri).
            $table->text('rincian_anggaran')->nullable();
            $table->string('bukti_anggaran')->nullable();

            //untuk pengunggahan file LPJ, wajib diisi, opsional boleh kosong
            $table->string('file_wajib')->nullable();
            $table->string('file_opsional')->nullable();


            // --- Alur status ---
            $table->enum('status', ['draft', 'diajukan', 'direvisi', 'disahkan'])->default('draft');
            $table->text('catatan_ketua')->nullable();
            $table->foreignId('disahkan_oleh')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('diajukan_pada')->nullable();
            $table->timestamp('disahkan_pada')->nullable();

            $table->timestamps();

            // Satu divisi cuma punya satu LPJ per periode.
            $table->unique(['periode_id', 'divisi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpjs');
    }
};
