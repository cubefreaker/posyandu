<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisImunisasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Hepatitis B-0', 'usia_pemberian' => '0-24 jam', 'urutan' => 1],
            ['nama' => 'BCG', 'usia_pemberian' => '1 bulan', 'urutan' => 2],
            ['nama' => 'Polio 1 (OPV)', 'usia_pemberian' => '1 bulan', 'urutan' => 3],
            ['nama' => 'DPT-HB-Hib 1', 'usia_pemberian' => '2 bulan', 'urutan' => 4],
            ['nama' => 'Polio 2 (OPV)', 'usia_pemberian' => '2 bulan', 'urutan' => 5],
            ['nama' => 'DPT-HB-Hib 2', 'usia_pemberian' => '3 bulan', 'urutan' => 6],
            ['nama' => 'Polio 3 (OPV)', 'usia_pemberian' => '3 bulan', 'urutan' => 7],
            ['nama' => 'DPT-HB-Hib 3', 'usia_pemberian' => '4 bulan', 'urutan' => 8],
            ['nama' => 'Polio 4 (OPV)', 'usia_pemberian' => '4 bulan', 'urutan' => 9],
            ['nama' => 'IPV', 'usia_pemberian' => '4 bulan', 'urutan' => 10],
            ['nama' => 'Campak/MR 1', 'usia_pemberian' => '9 bulan', 'urutan' => 11],
            ['nama' => 'DPT-HB-Hib Lanjutan', 'usia_pemberian' => '18 bulan', 'urutan' => 12],
            ['nama' => 'Campak/MR 2', 'usia_pemberian' => '18 bulan', 'urutan' => 13],
        ];

        DB::table('jenis_imunisasi')->insert($data);
    }
}
