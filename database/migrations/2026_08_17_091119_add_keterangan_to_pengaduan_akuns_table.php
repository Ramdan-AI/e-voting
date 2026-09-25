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
            $table->text('keterangan')->nullable()->after('foto_selfie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduan_akuns', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
