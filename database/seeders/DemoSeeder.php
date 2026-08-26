<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Ibu;
use App\Models\Imunisasi;
use App\Models\JenisImunisasi;
use App\Models\Penimbangan;
use App\Models\Vitamin;
use App\Services\StatusGiziService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Bersihkan data lama agar fresh
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Penimbangan::truncate();
        Vitamin::truncate();
        Imunisasi::truncate();
        Anak::truncate();
        Ibu::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->command->info('Mulai generate data Ibu...');
        $ibus = [];
        for ($i = 0; $i < 50; $i++) {
            $ibus[] = Ibu::create([
                'nik' => $faker->unique()->numerify('3201##############'),
                'nama' => $faker->firstName('female') . ' ' . $faker->lastName,
                'tanggal_lahir' => $faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
                'alamat' => $faker->address,
            ]);
        }

        $this->command->info('Mulai generate data Anak, Penimbangan, Imunisasi, Vitamin (2025 - Agu 2026)...');
        $jenisImunisasi = JenisImunisasi::all();
        
        $startPeriod = Carbon::create(2025, 1, 1);
        $endPeriod = Carbon::create(2026, 8, 31);
        
        // Target Z-scores untuk mencakup semua status gizi
        // Buruk/Sangat Pendek, Kurang/Pendek, Baik/Normal, Lebih/Tinggi
        $zscoreTargets = [-3.5, -2.5, 0, 2.5]; 
        
        for ($i = 0; $i < 150; $i++) { // 150 anak
            $ibu = $faker->randomElement($ibus);
            $jk = $faker->randomElement(['L', 'P']);
            
            // Tanggal lahir antara Jan 2020 sampai Jul 2026
            $tanggalLahir = Carbon::parse($faker->dateTimeBetween('2020-01-01', '2026-07-31')->format('Y-m-d'));
            
            $anak = Anak::create([
                'ibu_id' => $ibu->id,
                'nama' => $faker->firstName($jk == 'L' ? 'male' : 'female') . ' ' . $faker->lastName,
                'tanggal_lahir' => $tanggalLahir->format('Y-m-d'),
                'jenis_kelamin' => $jk,
            ]);

            // Profil z-score ditetapkan di awal per anak, sehingga kurva pertumbuhannya konsisten (dengan sedikit noise)
            $profilZscoreBbu = $faker->randomElement($zscoreTargets);
            $profilZscoreTbu = $faker->randomElement($zscoreTargets);

            $currentMonth = $startPeriod->copy();
            while ($currentMonth <= $endPeriod) {
                if ($currentMonth >= $tanggalLahir->copy()->startOfMonth()) {
                    $usiaBulan = (int) $tanggalLahir->diffInMonths($currentMonth);
                    
                    if ($usiaBulan <= 60) {
                        // Tambahkan noise agar grafik terlihat dinamis dan kadang bisa berubah status
                        $noiseBbu = $faker->randomFloat(2, -0.4, 0.4);
                        $noiseTbu = $faker->randomFloat(2, -0.4, 0.4);
                        
                        $bbTargetZ = $profilZscoreBbu + $noiseBbu;
                        $tbTargetZ = $profilZscoreTbu + $noiseTbu;

                        $refBbu = $this->getRef($usiaBulan, 'bbu', $jk);
                        $refTbu = $this->getRef($usiaBulan, 'tbu', $jk);

                        // Reverse rumus Z-score: X = Z * SD + Median
                        $beratBadan = ($bbTargetZ * $refBbu['sd']) + $refBbu['median'];
                        $tinggiBadan = ($tbTargetZ * $refTbu['sd']) + $refTbu['median'];

                        // Batas bawah logis
                        if ($beratBadan < 2.0) $beratBadan = 2.0;
                        if ($tinggiBadan < 40.0) $tinggiBadan = 40.0;

                        $status = StatusGiziService::hitung($usiaBulan, $jk, round($beratBadan, 1), round($tinggiBadan, 1));
                        
                        Penimbangan::create(array_merge([
                            'anak_id' => $anak->id,
                            'tanggal_pelayanan' => $currentMonth->copy()->addDays($faker->numberBetween(1, 20))->format('Y-m-d'),
                            'berat_badan' => round($beratBadan, 1),
                            'tinggi_badan' => round($tinggiBadan, 1),
                            'lingkar_kepala' => round(40 + ($usiaBulan * 0.1) + $faker->randomFloat(1, -1, 1), 1),
                            'lila' => round(13 + ($usiaBulan * 0.05) + $faker->randomFloat(1, -0.5, 0.5), 1),
                        ], $status));
                    }
                }
                
                // Cek jadwal Vitamin (Februari dan Agustus) untuk anak 6-59 bulan
                if (in_array($currentMonth->month, [2, 8])) {
                    $usiaBulan = (int) $tanggalLahir->diffInMonths($currentMonth);
                    if ($usiaBulan >= 6 && $usiaBulan <= 59) {
                        $jenisVitamin = ($usiaBulan >= 6 && $usiaBulan <= 11) ? 'kapsul_biru' : 'kapsul_merah';
                        Vitamin::firstOrCreate([
                            'anak_id' => $anak->id,
                            'jenis_vitamin' => $jenisVitamin,
                            'tanggal_pemberian' => $currentMonth->copy()->addDays($faker->numberBetween(1, 20))->format('Y-m-d'),
                        ]);
                    }
                }

                $currentMonth->addMonth();
            }

            // Imunisasi acak berdasarkan usia saat itu
            if ($jenisImunisasi->isNotEmpty()) {
                $imunisasiCount = $faker->numberBetween(1, min(5, $jenisImunisasi->count()));
                $randomJenis = $jenisImunisasi->random($imunisasiCount);
                foreach ($randomJenis as $jenis) {
                    $tglImunisasi = $tanggalLahir->copy()->addMonths($faker->numberBetween(1, 18));
                    if ($tglImunisasi <= $endPeriod) {
                        Imunisasi::firstOrCreate([
                            'anak_id' => $anak->id,
                            'jenis_imunisasi_id' => $jenis->id,
                        ], [
                            'tanggal_imunisasi' => $tglImunisasi->format('Y-m-d'),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Demo data berhasil di-generate! (Mencakup semua status gizi)');
    }

    // =========================================================================
    // HELPER: Tabel WHO untuk generate Data Balikan (Reverse Z-score)
    // =========================================================================

    private function getRef(int $bulan, string $indeks, string $jk): array
    {
        $tables = $this->getTables();
        $key = "{$indeks}_{$jk}";
        $table = $tables[$key];
        $bulan = max(0, min($bulan, 60));

        if (isset($table[$bulan])) return $table[$bulan];

        $keys = array_keys($table);
        $lower = null; $upper = null;
        foreach ($keys as $k) {
            if ($k <= $bulan) $lower = $k;
            if ($k >= $bulan && $upper === null) $upper = $k;
        }

        if ($lower === null) return $table[min($keys)];
        if ($upper === null) return $table[max($keys)];
        if ($lower === $upper) return $table[$lower];

        $t = ($bulan - $lower) / ($upper - $lower);
        return [
            'median' => $table[$lower]['median'] + $t * ($table[$upper]['median'] - $table[$lower]['median']),
            'sd'     => $table[$lower]['sd'] + $t * ($table[$upper]['sd'] - $table[$lower]['sd']),
        ];
    }

    private function getTables(): array
    {
        return [
            'bbu_L' => [
                0  => ['median' => 3.3,  'sd' => 0.45], 1  => ['median' => 4.5,  'sd' => 0.55],
                2  => ['median' => 5.6,  'sd' => 0.63], 3  => ['median' => 6.4,  'sd' => 0.68],
                4  => ['median' => 7.0,  'sd' => 0.73], 5  => ['median' => 7.5,  'sd' => 0.77],
                6  => ['median' => 7.9,  'sd' => 0.81], 9  => ['median' => 9.2,  'sd' => 0.92],
                12 => ['median' => 9.6,  'sd' => 1.05], 18 => ['median' => 10.9, 'sd' => 1.18],
                24 => ['median' => 12.2, 'sd' => 1.35], 30 => ['median' => 13.3, 'sd' => 1.51],
                36 => ['median' => 14.3, 'sd' => 1.64], 42 => ['median' => 15.3, 'sd' => 1.76],
                48 => ['median' => 16.3, 'sd' => 1.88], 54 => ['median' => 17.3, 'sd' => 2.00],
                60 => ['median' => 18.3, 'sd' => 2.12],
            ],
            'bbu_P' => [
                0  => ['median' => 3.2,  'sd' => 0.42], 1  => ['median' => 4.2,  'sd' => 0.51],
                2  => ['median' => 5.1,  'sd' => 0.58], 3  => ['median' => 5.8,  'sd' => 0.63],
                4  => ['median' => 6.4,  'sd' => 0.68], 5  => ['median' => 6.9,  'sd' => 0.72],
                6  => ['median' => 7.3,  'sd' => 0.76], 9  => ['median' => 8.5,  'sd' => 0.90],
                12 => ['median' => 8.9,  'sd' => 1.03], 18 => ['median' => 10.2, 'sd' => 1.17],
                24 => ['median' => 11.5, 'sd' => 1.33], 30 => ['median' => 12.7, 'sd' => 1.48],
                36 => ['median' => 13.9, 'sd' => 1.63], 42 => ['median' => 14.9, 'sd' => 1.76],
                48 => ['median' => 15.9, 'sd' => 1.89], 54 => ['median' => 16.9, 'sd' => 2.02],
                60 => ['median' => 17.9, 'sd' => 2.14],
            ],
            'tbu_L' => [
                0  => ['median' => 49.9, 'sd' => 1.89], 3  => ['median' => 61.4, 'sd' => 2.34],
                6  => ['median' => 67.6, 'sd' => 2.55], 9  => ['median' => 72.0, 'sd' => 2.69],
                12 => ['median' => 75.7, 'sd' => 2.76], 18 => ['median' => 82.3, 'sd' => 3.19],
                24 => ['median' => 87.8, 'sd' => 3.43], 30 => ['median' => 92.7, 'sd' => 3.73],
                36 => ['median' => 96.1, 'sd' => 3.80], 42 => ['median' => 99.9, 'sd' => 3.89],
                48 => ['median' => 103.3,'sd' => 3.97], 54 => ['median' => 106.4,'sd' => 4.04],
                60 => ['median' => 110.0,'sd' => 4.09],
            ],
            'tbu_P' => [
                0  => ['median' => 49.1, 'sd' => 1.86], 3  => ['median' => 59.8, 'sd' => 2.29],
                6  => ['median' => 65.7, 'sd' => 2.50], 9  => ['median' => 70.1, 'sd' => 2.65],
                12 => ['median' => 74.0, 'sd' => 2.82], 18 => ['median' => 80.7, 'sd' => 3.17],
                24 => ['median' => 86.4, 'sd' => 3.44], 30 => ['median' => 91.1, 'sd' => 3.74],
                36 => ['median' => 95.1, 'sd' => 3.79], 42 => ['median' => 98.7, 'sd' => 3.84],
                48 => ['median' => 102.7,'sd' => 3.96], 54 => ['median' => 105.9,'sd' => 4.05],
                60 => ['median' => 109.4,'sd' => 4.10],
            ],
        ];
    }
}
