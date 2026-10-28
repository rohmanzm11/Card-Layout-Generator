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
        Schema::create('print_kartus', function (Blueprint $table) {
           $table->id();
            
            // Opsi yang paling aman: referensi ke ID (bukan nomor)
            $table->foreignId('kartu_k_s_id')->nullable()->constrained('kartu_k_s')->nullOnDelete();
            $table->foreignId('kartu_b_s_id')->nullable()->constrained('kartu_b_s')->nullOnDelete();
            
            $table->foreignId('template_id')->constrained('templates')->cascadeOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_kartus');
    }
};
