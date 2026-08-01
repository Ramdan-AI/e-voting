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
        Schema::create('audit_log_admins', function (Blueprint $table) {
            $table->id();
            // Nullable: kalau suatu saat ada aksi otomatis oleh sistem
            // (mis. scheduler ubah status), admin_id tetap bisa kosong
            // sementara aksinya tetap tercatat.
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('set null');
            $table->string('aksi', 100);
            $table->text('keterangan')->nullable();
            // Cuma created_at, tanpa updated_at -- konsisten dengan
            // audit_log_presensis: log tidak pernah diubah setelah dibuat.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log_admins');
    }
};
