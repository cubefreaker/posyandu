<?php

namespace App\Services;

/**
 * Kalkulasi z-score status gizi berdasarkan standar Kemenkes/WHO.
 * Tabel referensi menggunakan median dan SD dari standar WHO 2006.
 */
class StatusGiziService
{
    /**
     * Hitung semua z-score dan tentukan status gizi.
     *
     * @param int    $usiaInBulan
     * @param string $jenisKelamin  'L' atau 'P'
     * @param float  $beratBadan    dalam kg
     * @param float  $tinggiBadan   dalam cm
     * @return array
     */
    public static function hitung(int $usiaInBulan, string $jenisKelamin, float $beratBadan, float $tinggiBadan): array
    {
        $jk = strtoupper($jenisKelamin);

        $zscoreBbu = self::hitungZscore($usiaInBulan, $beratBadan, 'bbu', $jk);
        $zscoreTbu = self::hitungZscore($usiaInBulan, $tinggiBadan, 'tbu', $jk);
        $zscoreBbtb = self::hitungZscoreBbtb($tinggiBadan, $beratBadan, $jk);

        return [
            'zscore_bbu'  => round($zscoreBbu, 2),
            'zscore_tbu'  => round($zscoreTbu, 2),
            'zscore_bbtb' => round($zscoreBbtb, 2),
            'status_bbu'  => self::kategoriiBbu($zscoreBbu),
            'status_tbu'  => self::kategoriTbu($zscoreTbu),
            'status_bbtb' => self::kategoriBbtb($zscoreBbtb),
        ];
    }

    /**
     * Hitung z-score dengan formula: Z = (X - median) / SD
     * Menggunakan pendekatan LMS (simplified) berdasarkan tabel WHO.
     */
    private static function hitungZscore(int $bulan, float $nilai, string $indeks, string $jk): float
    {
        $ref = self::getRef($bulan, $indeks, $jk);
        if (!$ref || $ref['sd'] == 0) return 0;

        return ($nilai - $ref['median']) / $ref['sd'];
    }

    private static function hitungZscoreBbtb(float $tinggi, float $berat, string $jk): float
    {
        // Cari referensi BB/TB berdasarkan tinggi (bukan usia)
        $ref = self::getRefBbtb($tinggi, $jk);
        if (!$ref || $ref['sd'] == 0) return 0;

        return ($berat - $ref['median']) / $ref['sd'];
    }

    private static function kategoriiBbu(float $z): string
    {
        if ($z < -3) return 'buruk';
        if ($z < -2) return 'kurang';
        if ($z <= 2)  return 'baik';
        return 'lebih';
    }

    private static function kategoriTbu(float $z): string
    {
        if ($z < -3) return 'sangat_pendek';
        if ($z < -2) return 'pendek';
        if ($z <= 2)  return 'normal';
        return 'tinggi';
    }

    private static function kategoriBbtb(float $z): string
    {
        if ($z < -3) return 'gizi_buruk';
        if ($z < -2) return 'gizi_kurang';
        if ($z <= 2)  return 'gizi_baik';
        return 'gizi_lebih';
    }

    /**
     * Tabel referensi BB/U dan TB/U (WHO 2006) — median dan SD per bulan.
     * Format: [bulan => [median, sd]]
     */
    public static function getRef(int $bulan, string $indeks, string $jk): ?array
    {
        $tables = self::getTables();
        $key = "{$indeks}_{$jk}";

        if (!isset($tables[$key])) return null;

        $table = $tables[$key];
        $bulan = max(0, min($bulan, 60));

        // Interpolasi linear jika usia tepat ada
        if (isset($table[$bulan])) {
            return $table[$bulan];
        }

        // Cari nilai terdekat
        $keys = array_keys($table);
        $lower = null;
        $upper = null;
        foreach ($keys as $k) {
            if ($k <= $bulan) $lower = $k;
            if ($k >= $bulan && $upper === null) $upper = $k;
        }

        if ($lower === null) return $table[min($keys)];
        if ($upper === null) return $table[max($keys)];
        if ($lower === $upper) return $table[$lower];

        // Interpolasi
        $t = ($bulan - $lower) / ($upper - $lower);
        return [
            'median' => $table[$lower]['median'] + $t * ($table[$upper]['median'] - $table[$lower]['median']),
            'sd'     => $table[$lower]['sd'] + $t * ($table[$upper]['sd'] - $table[$lower]['sd']),
        ];
    }

    private static function getRefBbtb(float $tinggi, string $jk): ?array
    {
        $tables = self::getTables();
        $key = "bbtb_{$jk}";

        if (!isset($tables[$key])) return null;

        $table = $tables[$key];
        $heights = array_keys($table);

        $lower = null;
        $upper = null;
        foreach ($heights as $h) {
            if ($h <= $tinggi) $lower = $h;
            if ($h >= $tinggi && $upper === null) $upper = $h;
        }

        if ($lower === null) return $table[min($heights)];
        if ($upper === null) return $table[max($heights)];
        if ($lower === $upper) return $table[$lower];

        $t = ($tinggi - $lower) / ($upper - $lower);
        return [
            'median' => $table[$lower]['median'] + $t * ($table[$upper]['median'] - $table[$lower]['median']),
            'sd'     => $table[$lower]['sd'] + $t * ($table[$upper]['sd'] - $table[$lower]['sd']),
        ];
    }

    /**
     * Tabel referensi WHO 2006 (nilai representatif per interval).
     * BB/U: berat badan per umur (kg)
     * TB/U: tinggi badan per umur (cm)
     * BB/TB: berat badan per tinggi badan (kg per cm)
     */
    private static function getTables(): array
    {
        return [
            // ── BB/U Laki-laki ─────────────────────────────────────────
            'bbu_L' => [
                0  => ['median' => 3.3,  'sd' => 0.45],
                1  => ['median' => 4.5,  'sd' => 0.55],
                2  => ['median' => 5.6,  'sd' => 0.63],
                3  => ['median' => 6.4,  'sd' => 0.68],
                4  => ['median' => 7.0,  'sd' => 0.73],
                5  => ['median' => 7.5,  'sd' => 0.77],
                6  => ['median' => 7.9,  'sd' => 0.81],
                9  => ['median' => 9.2,  'sd' => 0.92],
                12 => ['median' => 9.6,  'sd' => 1.05],
                18 => ['median' => 10.9, 'sd' => 1.18],
                24 => ['median' => 12.2, 'sd' => 1.35],
                30 => ['median' => 13.3, 'sd' => 1.51],
                36 => ['median' => 14.3, 'sd' => 1.64],
                42 => ['median' => 15.3, 'sd' => 1.76],
                48 => ['median' => 16.3, 'sd' => 1.88],
                54 => ['median' => 17.3, 'sd' => 2.00],
                60 => ['median' => 18.3, 'sd' => 2.12],
            ],
            // ── BB/U Perempuan ─────────────────────────────────────────
            'bbu_P' => [
                0  => ['median' => 3.2,  'sd' => 0.42],
                1  => ['median' => 4.2,  'sd' => 0.51],
                2  => ['median' => 5.1,  'sd' => 0.58],
                3  => ['median' => 5.8,  'sd' => 0.63],
                4  => ['median' => 6.4,  'sd' => 0.68],
                5  => ['median' => 6.9,  'sd' => 0.72],
                6  => ['median' => 7.3,  'sd' => 0.76],
                9  => ['median' => 8.5,  'sd' => 0.90],
                12 => ['median' => 8.9,  'sd' => 1.03],
                18 => ['median' => 10.2, 'sd' => 1.17],
                24 => ['median' => 11.5, 'sd' => 1.33],
                30 => ['median' => 12.7, 'sd' => 1.48],
                36 => ['median' => 13.9, 'sd' => 1.63],
                42 => ['median' => 14.9, 'sd' => 1.76],
                48 => ['median' => 15.9, 'sd' => 1.89],
                54 => ['median' => 16.9, 'sd' => 2.02],
                60 => ['median' => 17.9, 'sd' => 2.14],
            ],
            // ── TB/U Laki-laki ─────────────────────────────────────────
            'tbu_L' => [
                0  => ['median' => 49.9, 'sd' => 1.89],
                3  => ['median' => 61.4, 'sd' => 2.34],
                6  => ['median' => 67.6, 'sd' => 2.55],
                9  => ['median' => 72.0, 'sd' => 2.69],
                12 => ['median' => 75.7, 'sd' => 2.76],
                18 => ['median' => 82.3, 'sd' => 3.19],
                24 => ['median' => 87.8, 'sd' => 3.43],
                30 => ['median' => 92.7, 'sd' => 3.73],
                36 => ['median' => 96.1, 'sd' => 3.80],
                42 => ['median' => 99.9, 'sd' => 3.89],
                48 => ['median' => 103.3,'sd' => 3.97],
                54 => ['median' => 106.4,'sd' => 4.04],
                60 => ['median' => 110.0,'sd' => 4.09],
            ],
            // ── TB/U Perempuan ─────────────────────────────────────────
            'tbu_P' => [
                0  => ['median' => 49.1, 'sd' => 1.86],
                3  => ['median' => 59.8, 'sd' => 2.29],
                6  => ['median' => 65.7, 'sd' => 2.50],
                9  => ['median' => 70.1, 'sd' => 2.65],
                12 => ['median' => 74.0, 'sd' => 2.82],
                18 => ['median' => 80.7, 'sd' => 3.17],
                24 => ['median' => 86.4, 'sd' => 3.44],
                30 => ['median' => 91.1, 'sd' => 3.74],
                36 => ['median' => 95.1, 'sd' => 3.79],
                42 => ['median' => 98.7, 'sd' => 3.84],
                48 => ['median' => 102.7,'sd' => 3.96],
                54 => ['median' => 105.9,'sd' => 4.05],
                60 => ['median' => 109.4,'sd' => 4.10],
            ],
            // ── BB/TB Laki-laki (tinggi cm => [median BB kg, SD]) ──────
            'bbtb_L' => [
                45  => ['median' => 2.4,  'sd' => 0.32],
                50  => ['median' => 3.4,  'sd' => 0.40],
                55  => ['median' => 4.7,  'sd' => 0.51],
                60  => ['median' => 6.0,  'sd' => 0.62],
                65  => ['median' => 7.1,  'sd' => 0.71],
                70  => ['median' => 8.2,  'sd' => 0.80],
                75  => ['median' => 9.1,  'sd' => 0.89],
                80  => ['median' => 10.0, 'sd' => 0.97],
                85  => ['median' => 11.0, 'sd' => 1.05],
                90  => ['median' => 12.0, 'sd' => 1.14],
                95  => ['median' => 13.1, 'sd' => 1.23],
                100 => ['median' => 14.2, 'sd' => 1.34],
                105 => ['median' => 15.5, 'sd' => 1.50],
                110 => ['median' => 16.9, 'sd' => 1.67],
            ],
            // ── BB/TB Perempuan ────────────────────────────────────────
            'bbtb_P' => [
                45  => ['median' => 2.4,  'sd' => 0.31],
                50  => ['median' => 3.3,  'sd' => 0.38],
                55  => ['median' => 4.5,  'sd' => 0.49],
                60  => ['median' => 5.7,  'sd' => 0.59],
                65  => ['median' => 6.8,  'sd' => 0.68],
                70  => ['median' => 7.8,  'sd' => 0.77],
                75  => ['median' => 8.7,  'sd' => 0.85],
                80  => ['median' => 9.6,  'sd' => 0.94],
                85  => ['median' => 10.6, 'sd' => 1.02],
                90  => ['median' => 11.6, 'sd' => 1.12],
                95  => ['median' => 12.8, 'sd' => 1.24],
                100 => ['median' => 14.0, 'sd' => 1.36],
                105 => ['median' => 15.3, 'sd' => 1.50],
                110 => ['median' => 16.8, 'sd' => 1.67],
            ],
        ];
    }
}
