<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_imunisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('usia_pemberian', 30);
            $table->integer('urutan');
        });

        Schema::create('imunisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->foreignId('jenis_imunisasi_id')->constrained('jenis_imunisasi')->restrictOnDelete();
            $table->date('tanggal_imunisasi');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['anak_id', 'jenis_imunisasi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imunisasi');
        Schema::dropIfExists('jenis_imunisasi');
    }
};
