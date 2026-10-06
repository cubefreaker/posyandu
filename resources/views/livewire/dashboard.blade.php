<?php

use App\Models\Anak;
use App\Models\Ibu;
use App\Models\Kehamilan;
use App\Models\Imunisasi;
use App\Models\Penimbangan;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Dashboard')] class extends Component
{
    public int $totalIbu = 0;
    public int $totalIbuHamil = 0;
    public int $totalAnak = 0;
    public int $penimbanganBulanIni = 0;
    public int $imunisasiBulanIni = 0;

    public function mount(): void
    {
        if (auth()->check() && auth()->user()->role === 'kader') {
            // session()->flash('info', 'Halaman Dashboard khusus untuk Admin. Anda dialihkan ke Pelayanan Terpadu.');
            $this->redirectRoute('pelayanan.index');
            return;
        }

        $bulanIni = Carbon::now();

        $this->totalIbu = Ibu::count();
        $this->totalIbuHamil = Kehamilan::where('status_kehamilan', 'aktif')->count();
        $this->totalAnak = Anak::count();
        $this->penimbanganBulanIni = Penimbangan::whereMonth('tanggal_pelayanan', $bulanIni->month)
            ->whereYear('tanggal_pelayanan', $bulanIni->year)
            ->distinct('anak_id')
            ->count('anak_id');
        $this->imunisasiBulanIni = Imunisasi::whereMonth('tanggal_imunisasi', $bulanIni->month)
            ->whereYear('tanggal_imunisasi', $bulanIni->year)
            ->count();
    }

    public function with(): array
    {
        // Distribusi status gizi dari penimbangan terakhir tiap anak
        $statusGizi = ['buruk' => 0, 'kurang' => 0, 'baik' => 0, 'lebih' => 0];

        $anakIds = Anak::pluck('id');
        foreach ($anakIds as $anakId) {
            $terakhir = Penimbangan::where('anak_id', $anakId)
                ->latest('tanggal_pelayanan')
                ->first();
            if ($terakhir) {
                $statusGizi[$terakhir->status_bbu] = ($statusGizi[$terakhir->status_bbu] ?? 0) + 1;
            }
        }

        // Anak belum ditimbang bulan ini
        $bulanIni = Carbon::now();
        $anakSudahTimbang = Penimbangan::whereMonth('tanggal_pelayanan', $bulanIni->month)
            ->whereYear('tanggal_pelayanan', $bulanIni->year)
            ->pluck('anak_id')
            ->unique();

        $anakBelumTimbang = Anak::with('ibu')
            ->whereNotIn('id', $anakSudahTimbang)
            ->get();

        return [
            'statusGizi' => $statusGizi,
            'anakBelumTimbang' => $anakBelumTimbang,
        ];
    }
};
?>
<div>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
        {{-- Total Ibu --}}
        <a href="{{ route('data-ibu.index') }}" wire:navigate class="block bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 border-l-4 border-l-primary-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="truncate">Total Ibu</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-primary-600">{{ $totalIbu }}</p>
        </a>

        {{-- Ibu Hamil Aktif --}}
        <a href="{{ route('kesehatan-ibu.index') }}" wire:navigate class="block bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 border-l-4 border-l-pink-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 shrink-0 text-pink-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                <span class="truncate">Ibu Hamil</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-pink-600">{{ $totalIbuHamil }}</p>
            <p class="text-[11px] text-slate-400 mt-1 truncate">kehamilan aktif</p>
        </a>

        {{-- Total Anak --}}
        <a href="{{ route('data-anak.index') }}" wire:navigate class="block bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 border-l-4 border-l-secondary-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5"/><path d="M19.5 10c.3 0 .5.1.7.3.2.2.3.4.3.7 0 3.9-3.1 7-7 7s-7-3.1-7-7c0-.3.1-.5.3-.7.2-.2.4-.3.7-.3"/><path d="M12 2a2.5 2.5 0 0 0 0 5 2.5 2.5 0 0 0 0-5z"/><path d="M17.5 10c-.4-2.3-2.4-5-5.5-5s-5.1 2.7-5.5 5"/></svg>
                <span class="truncate">Total Anak</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-secondary-500">{{ $totalAnak }}</p>
        </a>

        {{-- Penimbangan Bulan Ini --}}
        <a href="{{ route('penimbangan.index') }}" wire:navigate class="block bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 border-l-4 border-l-emerald-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>
                <span class="truncate">Penimbangan</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-emerald-600">{{ $penimbanganBulanIni }}</p>
            <p class="text-[11px] text-slate-400 mt-1 truncate">anak bulan ini</p>
        </a>

        {{-- Imunisasi Bulan Ini --}}
        <a href="{{ route('imunisasi.index') }}" wire:navigate class="block col-span-2 sm:col-span-1 lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 border-l-4 border-l-blue-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4"/><path d="m17 7 3-3"/><path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/><path d="m9 11 4 4"/><path d="m5 19-3 3"/><path d="m14 4 6 6"/></svg>
                <span class="truncate">Imunisasi</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $imunisasiBulanIni }}</p>
            <p class="text-[11px] text-slate-400 mt-1 truncate">pemberian bulan ini</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Status Gizi Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="font-heading font-semibold text-slate-800 mb-4">Distribusi Status Gizi (BB/U)</h3>
            @if(array_sum($statusGizi) > 0)
                <canvas id="statusGiziChart" class="w-full" style="max-height: 280px;"></canvas>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-12 h-12 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                    <p class="text-sm font-medium">Belum ada data penimbangan</p>
                </div>
            @endif
        </div>

        {{-- Anak Belum Ditimbang --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
            <h3 class="font-heading font-semibold text-slate-800 mb-4">Anak Belum Ditimbang Bulan Ini</h3>
            @if($anakBelumTimbang->count() > 0)
                <div class="space-y-2 max-h-72 overflow-y-auto">
                    @foreach($anakBelumTimbang as $anak)
                        <div class="flex items-center justify-between p-3 bg-amber-50 border border-amber-100 rounded-xl gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $anak->nama }}</p>
                                <p class="text-xs text-slate-500 truncate">Ibu: {{ $anak->ibu->nama ?? '-' }} • {{ $anak->usia }}</p>
                            </div>
                            <a href="{{ route('penimbangan.create', ['anak_id' => $anak->id]) }}" wire:navigate class="text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 px-3 py-1.5 rounded-lg transition-colors shrink-0">
                                Timbang
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-12 h-12 mb-3 text-green-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p class="text-sm font-medium text-green-600">Semua anak sudah ditimbang</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Chart.js --}}
    @if(array_sum($statusGizi) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('statusGiziChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Gizi Buruk', 'Gizi Kurang', 'Gizi Baik', 'Gizi Lebih'],
                    datasets: [{
                        data: [{{ $statusGizi['buruk'] }}, {{ $statusGizi['kurang'] }}, {{ $statusGizi['baik'] }}, {{ $statusGizi['lebih'] }}],
                        backgroundColor: ['#FEF2F2', '#FFFBEB', '#F0FDF4', '#EFF6FF'],
                        borderColor: ['#DC2626', '#D97706', '#16A34A', '#2563EB'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } }
                    }
                }
            });
        });
    </script>
    @endif
</div>
