<?php

namespace Database\Seeders;

use App\Models\Ibu;
use App\Models\Kehamilan;
use App\Models\PemeriksaanKehamilan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KehamilanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil beberapa ibu untuk dibuatkan data kehamilan
        $ibuList = Ibu::take(8)->get();
        if ($ibuList->isEmpty()) return;

        // Sample 1: Ibu dengan kenaikan BB normal (G1)
        $ibu1 = $ibuList[0];
        $k1 = Kehamilan::create([
            'ibu_id' => $ibu1->id,
            'kehamilan_ke' => 1,
            'hpht' => now()->subWeeks(28)->format('Y-m-d'),
            'hpl' => now()->addWeeks(12)->format('Y-m-d'),
            'bb_sebelum_hamil' => 50.0,
            'tinggi_badan' => 155.0,
            'imt_pra_hamil' => 20.81,
            'kategori_imt' => 'normal',
            'lila_awal' => 24.5,
            'status_kek' => false,
            'status_kehamilan' => 'aktif',
            'catatan_risiko' => 'Kondisi ibu sehat',
        ]);

        // Kunjungan ANC untuk Ibu 1 (Minggu 8, 12, 16, 20, 24, 28)
        $kunjungan1 = [
            ['minggu' => 8,  'tgl' => now()->subWeeks(20), 'bb' => 50.8, 'tensi_s' => 110, 'tensi_d' => 70, 'tfu' => null, 'djj' => null, 'fe' => 30, 'hb' => 12.0],
            ['minggu' => 12, 'tgl' => now()->subWeeks(16), 'bb' => 51.5, 'tensi_s' => 120, 'tensi_d' => 80, 'tfu' => null, 'djj' => null, 'fe' => 30, 'hb' => 12.0],
            ['minggu' => 16, 'tgl' => now()->subWeeks(12), 'bb' => 53.0, 'tensi_s' => 120, 'tensi_d' => 80, 'tfu' => 16.0, 'djj' => 140,  'fe' => 30, 'hb' => 11.8],
            ['minggu' => 20, 'tgl' => now()->subWeeks(8),  'bb' => 54.8, 'tensi_s' => 115, 'tensi_d' => 75, 'tfu' => 19.0, 'djj' => 142,  'fe' => 30, 'hb' => 11.9],
            ['minggu' => 24, 'tgl' => now()->subWeeks(4),  'bb' => 56.5, 'tensi_s' => 120, 'tensi_d' => 80, 'tfu' => 22.0, 'djj' => 138,  'fe' => 30, 'hb' => 11.7],
            ['minggu' => 28, 'tgl' => now(),               'bb' => 58.2, 'tensi_s' => 120, 'tensi_d' => 80, 'tfu' => 25.0, 'djj' => 140,  'fe' => 30, 'hb' => 12.1],
        ];

        foreach ($kunjungan1 as $kj) {
            PemeriksaanKehamilan::create([
                'kehamilan_id' => $k1->id,
                'tanggal_periksa' => $kj['tgl']->format('Y-m-d'),
                'usia_kehamilan_minggu' => $kj['minggu'],
                'trimester' => $kj['minggu'] <= 13 ? 1 : ($kj['minggu'] <= 27 ? 2 : 3),
                'berat_badan' => $kj['bb'],
                'kenaikan_bb' => round($kj['bb'] - 50.0, 2),
                'tekanan_darah_sistol' => $kj['tensi_s'],
                'tekanan_darah_diastol' => $kj['tensi_d'],
                'lila' => 24.5,
                'tinggi_fundus' => $kj['tfu'],
                'djj' => $kj['djj'],
                'letak_janin' => $kj['minggu'] >= 24 ? 'Kepala (Preskep)' : 'Belum Teraba',
                'status_tt' => $kj['minggu'] >= 12 ? 'T2' : 'T1',
                'tablet_fe' => $kj['fe'],
                'hb' => $kj['hb'],
                'protein_urin' => 'negatif',
                'keluhan' => $kj['minggu'] == 8 ? 'Mual ringan di pagi hari' : 'Tidak ada keluhan',
                'tindakan_nasihat' => 'Konsumsi tablet Fe rutin malam hari dan asupan gizi seimbang.',
            ]);
        }

        // Sample 2: Ibu dengan risiko KEK (LiLA < 23.5)
        if ($ibuList->count() > 1) {
            $ibu2 = $ibuList[1];
            $k2 = Kehamilan::create([
                'ibu_id' => $ibu2->id,
                'kehamilan_ke' => 2,
                'hpht' => now()->subWeeks(20)->format('Y-m-d'),
                'hpl' => now()->addWeeks(20)->format('Y-m-d'),
                'bb_sebelum_hamil' => 42.0,
                'tinggi_badan' => 152.0,
                'imt_pra_hamil' => 18.18,
                'kategori_imt' => 'kurus',
                'lila_awal' => 22.0,
                'status_kek' => true,
                'status_kehamilan' => 'aktif',
                'catatan_risiko' => 'Risiko KEK (LiLA 22 cm), perlu PMT pemulihan',
            ]);

            PemeriksaanKehamilan::create([
                'kehamilan_id' => $k2->id,
                'tanggal_periksa' => now()->subWeeks(8)->format('Y-m-d'),
                'usia_kehamilan_minggu' => 12,
                'trimester' => 1,
                'berat_badan' => 43.0,
                'kenaikan_bb' => 1.0,
                'tekanan_darah_sistol' => 100,
                'tekanan_darah_diastol' => 70,
                'lila' => 22.0,
                'tinggi_fundus' => null,
                'djj' => null,
                'letak_janin' => 'Belum Teraba',
                'status_tt' => 'T2',
                'tablet_fe' => 30,
                'hb' => 10.8,
                'protein_urin' => 'negatif',
                'keluhan' => 'Nafsu makan berkurang',
                'tindakan_nasihat' => 'Diberikan biskuit PMT Ibu Hamil dan konseling gizi tinggi kalori protein.',
            ]);

            PemeriksaanKehamilan::create([
                'kehamilan_id' => $k2->id,
                'tanggal_periksa' => now()->format('Y-m-d'),
                'usia_kehamilan_minggu' => 20,
                'trimester' => 2,
                'berat_badan' => 45.2,
                'kenaikan_bb' => 3.2,
                'tekanan_darah_sistol' => 110,
                'tekanan_darah_diastol' => 70,
                'lila' => 22.5,
                'tinggi_fundus' => 18.0,
                'djj' => 144,
                'letak_janin' => 'Kepala (Preskep)',
                'status_tt' => 'T3',
                'tablet_fe' => 30,
                'hb' => 11.2,
                'protein_urin' => 'negatif',
                'keluhan' => 'Nafsu makan mulai membaik',
                'tindakan_nasihat' => 'Teruskan konsumsi PMT dan tablet Fe.',
            ]);
        }

        // Sample 3: Ibu Hamil Trimester 3 (Mendekati HPL)
        if ($ibuList->count() > 2) {
            $ibu3 = $ibuList[2];
            Kehamilan::create([
                'ibu_id' => $ibu3->id,
                'kehamilan_ke' => 1,
                'hpht' => now()->subWeeks(36)->format('Y-m-d'),
                'hpl' => now()->addWeeks(4)->format('Y-m-d'),
                'bb_sebelum_hamil' => 54.0,
                'tinggi_badan' => 158.0,
                'imt_pra_hamil' => 21.63,
                'kategori_imt' => 'normal',
                'lila_awal' => 25.0,
                'status_kek' => false,
                'status_kehamilan' => 'aktif',
                'catatan_risiko' => 'Persiapan persalinan P4K',
            ]);
        }
    }
}
