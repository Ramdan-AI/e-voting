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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            // 3 role sesuai struktur panitia inti yang sudah disepakati.
            // Superadmin BELUM dimasukkan di sini karena posisinya masih
            // dibahas terpisah (bukan bagian dari 3 akun panitia tahunan).
            $table->enum('role', ['ketua_pelaksana', 'koordinator_teknis', 'koordinator_acara']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
