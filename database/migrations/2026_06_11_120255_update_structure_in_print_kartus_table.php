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
        Schema::table('print_kartus', function (Blueprint $table) {
            // 1. Tambah kolom jenis_kartu setelah kolom id
            if (!Schema::hasColumn('print_kartus', 'jenis_kartu')) {
                $table->enum('jenis_kartu', ['K', 'B'])
                      ->after('id')
                      ->comment('K = Kartu K, B = Kartu B');
            }

            // 2. Tambah kolom range_start dan range_end setelah kolom kartu_b_s_id
            if (!Schema::hasColumn('print_kartus', 'range_start')) {
                $table->unsignedTinyInteger('range_start')
                      ->nullable()
                      ->after('kartu_b_s_id')
                      ->comment('Rentang awal nomor (1-100), hanya untuk Kartu K');
            }

            if (!Schema::hasColumn('print_kartus', 'range_end')) {
                $table->unsignedTinyInteger('range_end')
                      ->nullable()
                      ->after('range_start')
                      ->comment('Rentang akhir nomor (1-100), hanya untuk Kartu K');
            }
            
            // 3. Mengubah template_id menjadi nullable (opsional, jika diperlukan fleksibilitas)
            $table->foreignId('template_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_kartus', function (Blueprint $table) {
            // Hapus kolom-kolom yang tadi ditambahkan jika di-rollback
            $columns = ['jenis_kartu', 'range_start', 'range_end'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('print_kartus', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Kembalikan template_id menjadi NOT NULL jika di-rollback
            $table->foreignId('template_id')->nullable(false)->change();
        });
    }
};