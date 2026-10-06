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
        // Tambahkan kolom telepon ke tabel ibu jika belum ada
        if (Schema::hasTable('ibu') && !Schema::hasColumn('ibu', 'telepon')) {
            Schema::table('ibu', function (Blueprint $table) {
                $table->string('telepon', 20)->nullable()->after('alamat');
            });
        }

        // Tabel Data Kehamilan (Profil Kehamilan Ibu Hamil)
        Schema::create('kehamilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ibu_id')->constrained('ibu')->cascadeOnDelete();
            $table->integer('kehamilan_ke')->default(1); // Gravida (G)
            $table->date('hpht'); // Hari Pertama Haid Terakhir
            $table->date('hpl'); // Hari Perkiraan Lahir (Taksiran Persalinan)
            $table->decimal('bb_sebelum_hamil', 5, 2); // kg
            $table->decimal('tinggi_badan', 5, 2); // cm
            $table->decimal('imt_pra_hamil', 4, 2); // Indeks Massa Tubuh pra-hamil
            $table->enum('kategori_imt', ['kurus', 'normal', 'lebih', 'obesitas']);
            $table->decimal('lila_awal', 5, 2)->nullable(); // Skrining LiLA awal (cm)
            $table->boolean('status_kek')->default(false); // Kurang Energi Kronis (LiLA < 23.5)
            $table->enum('status_kehamilan', ['aktif', 'melahirkan', 'keguguran'])->default('aktif');
            $table->text('catatan_risiko')->nullable();
            $table->timestamps();
        });

        // Tabel Pemeriksaan Kehamilan (Kunjungan ANC / Antenatal Care Standar 10T Buku KIA)
        Schema::create('pemeriksaan_kehamilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kehamilan_id')->constrained('kehamilan')->cascadeOnDelete();
            $table->date('tanggal_periksa');
            $table->integer('usia_kehamilan_minggu'); // Usia gestasi (minggu)
            $table->tinyInteger('trimester'); // 1, 2, atau 3
            $table->decimal('berat_badan', 5, 2); // BB saat periksa (kg)
            $table->decimal('kenaikan_bb', 5, 2); // Kenaikan BB dari sebelum hamil (kg)
            $table->integer('tekanan_darah_sistol')->nullable(); // mmHg (cth: 120)
            $table->integer('tekanan_darah_diastol')->nullable(); // mmHg (cth: 80)
            $table->decimal('lila', 5, 2)->nullable(); // cm
            $table->decimal('tinggi_fundus', 5, 2)->nullable(); // TFU dalam cm
            $table->integer('djj')->nullable(); // Denyut Jantung Janin (dpm)
            $table->string('letak_janin', 50)->nullable(); // Kepala (Preskep), Sungsang, Lintang
            $table->string('status_tt', 10)->nullable(); // T1, T2, T3, T4, T5
            $table->integer('tablet_fe')->nullable(); // Tablet Tambah Darah yang diberikan
            $table->decimal('hb', 4, 2)->nullable(); // Hemoglobin (g/dL)
            $table->enum('protein_urin', ['negatif', 'positif_1', 'positif_2', 'positif_3'])->nullable();
            $table->integer('gula_darah')->nullable(); // mg/dL
            $table->text('keluhan')->nullable();
            $table->text('tindakan_nasihat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_kehamilan');
        Schema::dropIfExists('kehamilan');

        if (Schema::hasTable('ibu') && Schema::hasColumn('ibu', 'telepon')) {
            Schema::table('ibu', function (Blueprint $table) {
                $table->dropColumn('telepon');
            });
        }
    }
};
