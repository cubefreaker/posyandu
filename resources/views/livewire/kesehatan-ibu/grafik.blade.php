<?php

use App\Models\Kehamilan;
use App\Models\PemeriksaanKehamilan;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public Kehamilan $kehamilan;
    public array $chartBands = [];
    public array $chartPoints = [];
    public ?string $statusKenaikanTerakhir = null;

    public function mount(int $kehamilanId): void
    {
        $this->kehamilan = Kehamilan::with(['ibu', 'pemeriksaan'])->findOrFail($kehamilanId);

        $kategori = $this->kehamilan->kategori_imt;

        // Parameter Kemenkes RI / IOM per Kategori IMT Pra-Hamil
        // [T1 min, T1 max, T2-T3 weekly min, T2-T3 weekly max, Total min, Total max]
        $params = match($kategori) {
            'kurus'    => ['t1_min' => 1.0, 't1_max' => 2.5, 'rate_min' => 0.44, 'rate_max' => 0.58, 'total_min' => 12.5, 'total_max' => 18.0],
            'normal'   => ['t1_min' => 0.8, 't1_max' => 2.0, 'rate_min' => 0.38, 'rate_max' => 0.50, 'total_min' => 11.5, 'total_max' => 16.0],
            'lebih'    => ['t1_min' => 0.5, 't1_max' => 1.5, 'rate_min' => 0.23, 'rate_max' => 0.35, 'total_min' => 7.0,  'total_max' => 11.5],
            'obesitas' => ['t1_min' => 0.2, 't1_max' => 1.0, 'rate_min' => 0.17, 'rate_max' => 0.27, 'total_min' => 5.0,  'total_max' => 9.0],
            default    => ['t1_min' => 0.8, 't1_max' => 2.0, 'rate_min' => 0.38, 'rate_max' => 0.50, 'total_min' => 11.5, 'total_max' => 16.0],
        };

        // Buat kurva pita batas bawah & batas atas dari minggu 0 s/d 40
        for ($w = 0; $w <= 40; $w++) {
            if ($w <= 13) {
                $bawah = ($params['t1_min'] / 13) * $w;
                $atas = ($params['t1_max'] / 13) * $w;
            } else {
                $bawah = $params['t1_min'] + ($params['rate_min'] * ($w - 13));
                $atas = $params['t1_max'] + ($params['rate_max'] * ($w - 13));
            }

            $this->chartBands[] = [
                'x' => $w,
                'min' => round($bawah, 2),
                'max' => round($atas, 2),
            ];
        }

        // Plot data riwayat pemeriksaan aktual
        $riwayat = $this->kehamilan->pemeriksaan->sortBy('tanggal_periksa');
        foreach ($riwayat as $p) {
            $this->chartPoints[] = [
                'x' => (int) $p->usia_kehamilan_minggu,
                'y' => (float) $p->kenaikan_bb,
                'bb' => (float) $p->berat_badan,
                'tgl' => $p->tanggal_periksa->format('d/m/Y'),
                'tensi' => $p->tekanan_darah,
            ];
        }

        // Evaluasi titik kunjungan terakhir
        $terakhir = $riwayat->last();
        if ($terakhir) {
            $w = (int) $terakhir->usia_kehamilan_minggu;
            $wIndex = min(40, max(0, $w));
            $band = $this->chartBands[$wIndex] ?? null;

            if ($band) {
                $kenaikan = (float) $terakhir->kenaikan_bb;
                if ($kenaikan < $band['min']) {
                    $this->statusKenaikanTerakhir = 'kurang';
                } elseif ($kenaikan > $band['max']) {
                    $this->statusKenaikanTerakhir = 'lebih';
                } else {
                    $this->statusKenaikanTerakhir = 'normal';
                }
            }
        }
    }

    public function with(): array
    {
        $riwayatPemeriksaan = $this->kehamilan->pemeriksaan()->latest('tanggal_periksa')->get();
        return compact('riwayatPemeriksaan');
    }

    public function title(): string
    {
        return 'Grafik Buku KIA — ' . ($this->kehamilan->ibu->nama ?? '');
    }
};
?>

<div>
    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('kesehatan-ibu.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h2 class="font-heading font-bold text-xl text-slate-900">Grafik Peningkatan Berat Badan Ibu Hamil</h2>
                <p class="text-xs text-slate-500">Standar Buku KIA (Buku Pink Kementerian Kesehatan RI) berdasarkan IMT Pra-Hamil</p>
            </div>
        </div>

        <div class="w-full sm:w-auto">
            <a href="{{ route('kesehatan-ibu.periksa', $kehamilan->id) }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all w-full sm:w-auto">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Input Pemeriksaan ANC
            </a>
        </div>
    </div>

    {{-- Kartu Info Ibu & Kategori IMT --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <span class="text-xs text-slate-400">Nama Ibu Hamil</span>
                <p class="text-sm font-bold text-slate-900">{{ $kehamilan->ibu->nama }}</p>
                <span class="text-[11px] text-slate-400">G{{ $kehamilan->kehamilan_ke }} · {{ $kehamilan->ibu->tanggal_lahir->age }} th</span>
            </div>

            <div>
                <span class="text-xs text-slate-400">Usia Kandungan</span>
                <p class="text-sm font-bold text-pink-600">{{ $kehamilan->usia_minggu }} Minggu</p>
                <span class="text-[11px] text-slate-400">Trimester {{ $kehamilan->trimester_saat_ini }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400">BB Sebelum Hamil</span>
                <p class="text-sm font-bold text-slate-900">{{ $kehamilan->bb_sebelum_hamil }} kg</p>
                <span class="text-[11px] text-slate-400">TB: {{ $kehamilan->tinggi_badan }} cm</span>
            </div>

            <div>
                <span class="text-xs text-slate-400">IMT Pra-Hamil</span>
                <p class="text-sm font-bold text-slate-900">{{ $kehamilan->imt_pra_hamil }} kg/m²</p>
                <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 capitalize">
                    {{ $kehamilan->label_kategori_imt }}
                </span>
            </div>

            <div class="col-span-2 sm:col-span-1 lg:col-span-1">
                <span class="text-xs text-slate-400">Target Kenaikan Total</span>
                @php
                    $targetStr = match($kehamilan->kategori_imt) {
                        'kurus'    => '12.5 – 18.0 kg',
                        'normal'   => '11.5 – 16.0 kg',
                        'lebih'    => '7.0 – 11.5 kg',
                        'obesitas' => '5.0 – 9.0 kg',
                        default    => '11.5 – 16.0 kg'
                    };
                @endphp
                <p class="text-sm font-bold text-emerald-700">{{ $targetStr }}</p>
                <span class="text-[11px] text-slate-400">s/d minggu ke-40</span>
            </div>
        </div>

        {{-- Evaluasi Status Terakhir Alert --}}
        @if($statusKenaikanTerakhir)
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($statusKenaikanTerakhir === 'normal')
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-xs font-bold text-green-700">Status Kenaikan BB Normal: Kenaikan berat badan ibu berada di dalam kurva rekomendasi Buku KIA.</span>
                    @elseif($statusKenaikanTerakhir === 'kurang')
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-amber-700">Status Kenaikan BB Kurang: Kenaikan BB berada di bawah batas minimum kurva. Waspada janin kecil / risiko BBLR. Berikan PMT & konseling gizi.</span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-bold text-blue-700">Status Kenaikan BB Berlebih: Kenaikan BB melampaui kurva rekomendasi. Waspada preeklampsia atau makrosomia (bayi besar).</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- GRAFIK KENAIKAN BERAT BADAN (CANVAS) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-heading font-bold text-slate-800 text-base">Kurva Kenaikan Berat Badan Ibu (kg) vs Usia Kehamilan (Minggu)</h3>
                <p class="text-xs text-slate-400">Area hijau/arsir merupakan rentang target ideal menurut Buku KIA Kemenkes RI</p>
            </div>
        </div>

        @if(count($chartPoints) > 0)
            <div class="h-[300px] sm:h-[380px] relative" wire:ignore>
                <canvas id="chartKiaBumil"></canvas>
            </div>
        @else
            <div class="py-14 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                <p class="text-sm font-semibold text-slate-600">Belum ada data kunjungan pemeriksaan ANC</p>
                <p class="text-xs">Klik tombol "+ Input Pemeriksaan ANC" di atas untuk mencatat berat badan dan kondisi ibu saat ini.</p>
            </div>
        @endif
    </div>

    {{-- TABEL RIWAYAT PEMERIKSAAN ANC --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-heading font-bold text-slate-800 text-base">Riwayat Pemeriksaan ANC Buku KIA</h3>
            <span class="text-xs text-slate-400">Total: {{ $riwayatPemeriksaan->count() }} Kunjungan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[880px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tgl Periksa</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Usia Gestasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">BB (kg)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kenaikan BB</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tensi (mmHg)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">TFU (cm)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">DJJ (dpm)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Letak Janin</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tablet Fe</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Hb</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($riwayatPemeriksaan as $p)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $p->tanggal_periksa->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-bold text-pink-600">{{ $p->usia_kehamilan_minggu }} mgg</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $p->berat_badan }}</td>
                            <td class="px-4 py-3 font-semibold {{ (float)$p->kenaikan_bb < 0 ? 'text-red-500' : 'text-emerald-700' }}">
                                {{ $p->kenaikan_bb > 0 ? "+{$p->kenaikan_bb}" : $p->kenaikan_bb }} kg
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs {{ (int)$p->tekanan_darah_sistol >= 140 ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                    {{ $p->tekanan_darah }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->tinggi_fundus ? $p->tinggi_fundus . ' cm' : '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->djj ? $p->djj . ' dpm' : '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->letak_janin ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->tablet_fe ? $p->tablet_fe . ' tab' : '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->hb ? $p->hb . ' g/dL' : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat kunjungan pemeriksaan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SCRIPT CHART.JS UNTUK KURVA BUKU KIA --}}
    @if(count($chartPoints) > 0)
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endassets

    @script
    <script>
        const initKiaChart = () => {
            if (typeof Chart === 'undefined') {
                setTimeout(initKiaChart, 100);
                return;
            }

            const canvas = document.getElementById('chartKiaBumil');
            if (!canvas) return;

            let existing = Chart.getChart('chartKiaBumil');
            if (existing) existing.destroy();

            const bands = $wire.chartBands;
            const points = $wire.chartPoints;
            const namaIbu = "{{ $kehamilan->ibu->nama }}";

            const minBandData = bands.map(b => ({ x: b.x, y: b.min }));
            const maxBandData = bands.map(b => ({ x: b.x, y: b.max }));

            new Chart(canvas, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Batas Bawah Rekomendasi (Min kg)',
                            data: minBandData,
                            borderColor: 'rgba(34, 197, 94, 0.4)',
                            backgroundColor: 'transparent',
                            borderWidth: 1.5,
                            borderDash: [4, 4],
                            pointRadius: 0,
                            tension: 0.3,
                            fill: false,
                        },
                        {
                            label: 'Rentang Rekomendasi Buku KIA Kemenkes',
                            data: maxBandData,
                            borderColor: 'rgba(34, 197, 94, 0.6)',
                            backgroundColor: 'rgba(34, 197, 94, 0.15)', // Shaded Green Band
                            borderWidth: 1.5,
                            pointRadius: 0,
                            tension: 0.3,
                            fill: '-1', // Fill down to dataset 0 (batas bawah)
                        },
                        {
                            label: 'Pertambahan Berat Badan: ' + namaIbu,
                            data: points,
                            borderColor: '#db2777', // Pink-600
                            backgroundColor: '#be185d',
                            pointBackgroundColor: '#be185d',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 2.5,
                            tension: 0.2,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 2) {
                                        const p = context.raw;
                                        return [
                                            'Tgl: ' + p.tgl + ' (' + p.x + ' minggu)',
                                            'Kenaikan BB: ' + (p.y > 0 ? '+' : '') + p.y + ' kg (BB: ' + p.bb + ' kg)',
                                            'Tensi: ' + p.tensi + ' mmHg'
                                        ];
                                    }
                                    return context.dataset.label + ': ' + context.raw.y + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: { display: true, text: 'Usia Kehamilan (Minggu Gestasi)' },
                            min: 0,
                            max: 40,
                            ticks: { stepSize: 4 },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        },
                        y: {
                            title: { display: true, text: 'Pertambahan Berat Badan (kg)' },
                            min: -2,
                            max: 22,
                            ticks: { stepSize: 2 },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        }
                    }
                }
            });
        };

        initKiaChart();
    </script>
    @endscript
    @endif
</div>
