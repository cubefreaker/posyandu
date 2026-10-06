<?php

use App\Models\Anak;
use App\Models\Penimbangan;
use App\Services\StatusGiziService;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public Anak $anak;
    public array $chartDataBb = [];
    public array $chartDataTb = [];
    public array $kmsBandsBb = [];
    public array $kmsBandsTb = [];
    public bool $hideHeader = false;
    public bool $hideTable = false;

    public function mount(int $anakId, bool $hideHeader = false, bool $hideTable = false): void
    {
        $this->hideHeader = $hideHeader;
        $this->hideTable = $hideTable;
        $this->anak = Anak::with(['ibu', 'penimbangan'])->findOrFail($anakId);

        $riwayat = $this->anak->penimbangan()
            ->orderBy('tanggal_pelayanan')
            ->get();

        $jk = $this->anak->jenis_kelamin;
        $tanggalLahir = $this->anak->tanggal_lahir;

        foreach ($riwayat as $p) {
            $usiaBulan = $tanggalLahir->diffInDays($p->tanggal_pelayanan) / 30.4375;
            $this->chartDataBb[] = [
                'x' => round($usiaBulan, 2),
                'y' => (float) $p->berat_badan,
                'tanggal' => $p->tanggal_pelayanan->format('d M Y')
            ];
            $this->chartDataTb[] = [
                'x' => round($usiaBulan, 2),
                'y' => (float) $p->tinggi_badan,
                'tanggal' => $p->tanggal_pelayanan->format('d M Y')
            ];
        }

        // Generate KMS bands (0 - 60 months)
        for ($i = 0; $i <= 60; $i++) {
            $refBb = StatusGiziService::getRef($i, 'bbu', $jk);
            if ($refBb) {
                $this->kmsBandsBb[] = [
                    'x' => $i,
                    'minus3' => round($refBb['median'] - 3 * $refBb['sd'], 2),
                    'minus2' => round($refBb['median'] - 2 * $refBb['sd'], 2),
                    'median' => round($refBb['median'], 2),
                    'plus2'  => round($refBb['median'] + 2 * $refBb['sd'], 2),
                    'plus3'  => round($refBb['median'] + 3 * $refBb['sd'], 2),
                ];
            }

            $refTb = StatusGiziService::getRef($i, 'tbu', $jk);
            if ($refTb) {
                $this->kmsBandsTb[] = [
                    'x' => $i,
                    'minus3' => round($refTb['median'] - 3 * $refTb['sd'], 2),
                    'minus2' => round($refTb['median'] - 2 * $refTb['sd'], 2),
                    'median' => round($refTb['median'], 2),
                    'plus2'  => round($refTb['median'] + 2 * $refTb['sd'], 2),
                    'plus3'  => round($refTb['median'] + 3 * $refTb['sd'], 2),
                ];
            }
        }
    }

    public function with(): array
    {
        $riwayat = $this->anak->penimbangan()
            ->orderByDesc('tanggal_pelayanan')
            ->get();

        return compact('riwayat');
    }

    public function title(): string
    {
        return 'Grafik KMS — ' . $this->anak->nama;
    }
};
?>
<div>
    @if(!$hideHeader)
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('penimbangan.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h2 class="font-heading font-bold text-xl text-slate-900">Grafik KMS — {{ $anak->nama }}</h2>
            <p class="text-sm text-slate-500">Ibu: {{ $anak->ibu->nama ?? '-' }} · Usia: {{ $anak->usia }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Grafik BB --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="font-heading font-semibold text-slate-700 mb-4">Berat Badan per Kunjungan (kg)</h3>
            @if(count($chartDataBb) > 0)
                <div class="h-[280px] sm:h-[350px] relative" wire:ignore>
                    <canvas id="chartBb-{{ $anak->id }}"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-10 h-10 mb-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <p class="text-sm">Belum ada data</p>
                </div>
            @endif
        </div>

        {{-- Grafik TB --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="font-heading font-semibold text-slate-700 mb-4">Tinggi Badan per Kunjungan (cm)</h3>
            @if(count($chartDataTb) > 0)
                <div class="h-[280px] sm:h-[350px] relative" wire:ignore>
                    <canvas id="chartTb-{{ $anak->id }}"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-10 h-10 mb-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <p class="text-sm">Belum ada data</p>
                </div>
            @endif
        </div>
    </div>

    @if(!$hideTable)
    {{-- Tabel riwayat --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-heading font-semibold text-slate-800">Riwayat Penimbangan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">BB (kg)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">TB (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">LK (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">LILA (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Z-score BB/U</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status BB/U</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status TB/U</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $p)
                        @php
                            $badgeClass = match($p->status_bbu) {
                                'buruk'  => 'bg-red-50 text-red-700 border border-red-200',
                                'kurang' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                'baik'   => 'bg-green-50 text-green-700 border border-green-200',
                                'lebih'  => 'bg-blue-50 text-blue-700 border border-blue-200',
                                default  => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->tanggal_pelayanan->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $p->berat_badan }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->tinggi_badan }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->lingkar_kepala ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->lila ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-600">{{ $p->zscore_bbu }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $p->label_status_bbu }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->label_status_tbu }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-400">Belum ada riwayat penimbangan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($chartDataBb) > 0)
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endassets

    @script
    <script>
        const initKmsChart = () => {
            if (typeof Chart === 'undefined') {
                setTimeout(initKmsChart, 100);
                return;
            }

            const childDataBb = $wire.chartDataBb;
            const childDataTb = $wire.chartDataTb;
            const bandsBb = $wire.kmsBandsBb;
            const bandsTb = $wire.kmsBandsTb;
            const namaAnak = "{{ $anak->nama }}";

            const getBandData = (bands, key) => bands.map(b => ({ x: b.x, y: b[key] }));

            const createKmsChart = (elementId, childData, bands, yLabel) => {
                const el = document.getElementById(elementId);
                if (!el) return;
                
                let existingChart = Chart.getChart(elementId);
                if (existingChart) existingChart.destroy();

                return new Chart(el, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: '< -3 SD (Buruk)',
                                data: getBandData(bands, 'minus3'),
                                borderColor: 'transparent',
                                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                                fill: 'origin',
                                pointRadius: 0,
                                borderWidth: 0,
                                tension: 0.4
                            },
                            {
                                label: '-3 SD s/d -2 SD (Kurang)',
                                data: getBandData(bands, 'minus2'),
                                borderColor: 'rgba(234, 179, 8, 0.5)',
                                backgroundColor: 'rgba(234, 179, 8, 0.15)',
                                fill: 0,
                                pointRadius: 0,
                                borderWidth: 1,
                                borderDash: [5, 5],
                                tension: 0.4
                            },
                            {
                                label: 'Median',
                                data: getBandData(bands, 'median'),
                                borderColor: '#22C55E',
                                backgroundColor: 'transparent',
                                fill: false,
                                pointRadius: 0,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                tension: 0.4
                            },
                            {
                                label: '-2 SD s/d +2 SD (Normal)',
                                data: getBandData(bands, 'plus2'),
                                borderColor: 'rgba(34, 197, 94, 0.5)',
                                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                                fill: 1,
                                pointRadius: 0,
                                borderWidth: 1,
                                tension: 0.4
                            },
                            {
                                label: '+2 SD s/d +3 SD (Lebih)',
                                data: getBandData(bands, 'plus3'),
                                borderColor: 'rgba(249, 115, 22, 0.5)',
                                backgroundColor: 'rgba(249, 115, 22, 0.15)',
                                fill: 3,
                                pointRadius: 0,
                                borderWidth: 1,
                                borderDash: [5, 5],
                                tension: 0.4
                            },
                            {
                                label: 'Pertumbuhan ' + namaAnak,
                                data: childData,
                                borderColor: '#2563EB',
                                backgroundColor: '#1D4ED8',
                                pointBackgroundColor: '#1D4ED8',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2,
                                fill: false,
                                tension: 0.3
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
                                    boxWidth: 6,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 5) {
                                            return context.raw.tanggal + ': ' + context.raw.y + ' ' + (yLabel.includes('Berat') ? 'kg' : 'cm');
                                        }
                                        return context.dataset.label + ': ' + context.raw.y.toFixed(1);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                title: { display: true, text: 'Usia (Bulan)' },
                                min: 0, max: 60,
                                ticks: { stepSize: 6 },
                                grid: { color: 'rgba(0,0,0,0.04)' }
                            },
                            y: {
                                title: { display: true, text: yLabel },
                                grid: { color: 'rgba(0,0,0,0.04)' }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                    }
                });
            };

            createKmsChart('chartBb-{{ $anak->id }}', childDataBb, bandsBb, 'Berat Badan (kg)');
            createKmsChart('chartTb-{{ $anak->id }}', childDataTb, bandsTb, 'Tinggi Badan (cm)');
        };

        initKmsChart();
    </script>
    @endscript
    @endif
</div>

