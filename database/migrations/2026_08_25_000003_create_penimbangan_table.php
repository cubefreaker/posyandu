<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penimbangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->date('tanggal_pelayanan');
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 2);
            $table->decimal('lingkar_kepala', 5, 2)->nullable();
            $table->decimal('lila', 5, 2)->nullable();
            $table->decimal('zscore_bbu', 4, 2);
            $table->decimal('zscore_tbu', 4, 2);
            $table->decimal('zscore_bbtb', 4, 2);
            $table->enum('status_bbu', ['buruk', 'kurang', 'baik', 'lebih']);
            $table->enum('status_tbu', ['sangat_pendek', 'pendek', 'normal', 'tinggi']);
            $table->enum('status_bbtb', ['gizi_buruk', 'gizi_kurang', 'gizi_baik', 'gizi_lebih']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penimbangan');
    }
};
