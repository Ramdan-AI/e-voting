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
        Schema::table('pengaduan_akuns', function (Blueprint $table) {
            $table->renameColumn('foto_selfie', 'foto_ktm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduan_akuns', function (Blueprint $table) {
            $table->renameColumn('foto_ktm', 'foto_selfie');
        });
    }
};
