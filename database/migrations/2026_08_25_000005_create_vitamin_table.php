<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vitamin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->date('tanggal_pemberian');
            $table->enum('jenis_vitamin', ['kapsul_biru', 'kapsul_merah']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vitamin');
    }
};
