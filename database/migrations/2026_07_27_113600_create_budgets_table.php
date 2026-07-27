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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Anggaran biasanya diikat ke kategori pengeluaran tertentu
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('limit_nominal', 15, 2); // Batas anggaran, misal 2000000
            $table->integer('bulan'); // 1-12
            $table->integer('tahun'); // Contoh: 2026
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
