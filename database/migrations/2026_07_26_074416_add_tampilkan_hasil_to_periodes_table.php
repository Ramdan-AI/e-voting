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
        Schema::table('periodes', function (Blueprint $table) {
            // Toggle untuk fitur "Toggle Grafik Hasil per Kandidat":
            // grafik hasil default tersembunyi di landing page publik
            // sampai admin membuka toggle ini secara manual.
            $table->boolean('tampilkan_hasil')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            $table->dropColumn('tampilkan_hasil');
        });
    }
};
