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
        Schema::table('kartu_k_s', function (Blueprint $table) {
            // Menambahkan kolom qr_code setelah kolom nomor (nullable agar data lama tidak error jika kosong)
            $table->string('qr_code')->nullable()->after('nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_k_s', function (Blueprint $table) {
            // Menghapus kolom qr_code jika migration di-rollback
            $table->dropColumn('qr_code');
        });
    }
};