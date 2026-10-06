<?php

use App\Models\Anak;
use App\Models\Ibu;
use App\Models\Imunisasi;
use App\Models\Penimbangan;
use App\Models\Vitamin;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Laporan')] class extends Component
{
    public string $startDate;
    public string $endDate;
    public string $tipeLaporan = 'periode'; // periode, ibu, anak
    public string $ibuId = '';
    public string $anakId = '';
    public string $searchIbu = '';
    public string $searchAnak = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function selectIbu(int $id): void
    {
        $this->ibuId = (string) $id;
        $this->searchIbu = '';
    }

    public function resetIbu(): void
    {
        $this->ibuId = '';
        $this->searchIbu = '';
    }

    public function selectAnak(int $id): void
    {
        $this->anakId = (string) $id;
        $this->searchAnak = '';
    }

    public function resetAnak(): void
    {
        $this->anakId = '';
        $this->searchAnak = '';
    }

    public function with(): array
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $startDateTime = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endDateTime = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        $daftarIbu = [];
        $daftarAnak = [];
        $dataIbu = null;
        $dataAnak = null;
        $rekap = [];

        if ($this->tipeLaporan === 'periode') {
            // Penimbangan periode ini
            $penimbanganPeriode = Penimbangan::with('anak')
                ->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])
                ->get();

            // Status gizi per anak (penimbangan terakhir periode ini)
            $statusGizi = ['buruk' => 0, 'kurang' => 0, 'baik' => 0, 'lebih' => 0];
            foreach ($penimbanganPeriode->groupBy('anak_id') as $perAnak) {
                $terakhir = $perAnak->sortByDesc('tanggal_pelayanan')->first();
                if ($terakhir) {
                    $statusGizi[$terakhir->status_bbu] = ($statusGizi[$terakhir->status_bbu] ?? 0) + 1;
                }
            }

            // Imunisasi periode ini
            $imunisasiPeriode = Imunisasi::with('jenisImunisasi')
                ->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime])
                ->get()
                ->groupBy('jenisImunisasi.nama');

            // Vitamin periode ini
            $vitaminPeriode = Vitamin::whereBetween('tanggal_pemberian', [$startDateTime, $endDateTime])
                ->get()
                ->groupBy('jenis_vitamin');

            $rekap = [
                'totalIbu'            => Ibu::count(),
                'totalAnak'           => Anak::count(),
                'totalPenimbangan'    => $penimbanganPeriode->unique('anak_id')->count(),
                'statusGizi'          => $statusGizi,
                'imunisasiPeriode'    => $imunisasiPeriode,
                'vitaminBiru'         => $vitaminPeriode->get('kapsul_biru', collect())->count(),
                'vitaminMerah'        => $vitaminPeriode->get('kapsul_merah', collect())->count(),
            ];
        } elseif ($this->tipeLaporan === 'ibu') {
            $daftarIbu = Ibu::when($this->searchIbu, fn ($q) => $q->where('nama', 'like', "%{$this->searchIbu}%"))
                ->orderBy('nama')
                ->limit(20)
                ->get();
            
            if ($this->ibuId) {
                $dataIbu = Ibu::with(['anak' => function($q) use ($startDateTime, $endDateTime) {
                    $q->with([
                        'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])->latest('tanggal_pelayanan'),
                        'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime]),
                    ]);
                }])->find($this->ibuId);
            }
        } elseif ($this->tipeLaporan === 'anak') {
            $daftarAnak = Anak::with('ibu')
                ->when($this->searchAnak, fn ($q) => $q->where('nama', 'like', "%{$this->searchAnak}%"))
                ->orderBy('nama')
                ->limit(20)
                ->get();
            
            if ($this->anakId) {
                $dataAnak = Anak::with([
                    'ibu',
                    'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])->orderBy('tanggal_pelayanan', 'desc'),
                    'imunisasi.jenisImunisasi',
                    'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime])->orderBy('tanggal_imunisasi', 'desc'),
                    'vitamin' => fn($q) => $q->whereBetween('tanggal_pemberian', [$startDateTime, $endDateTime])->orderBy('tanggal_pemberian', 'desc')
                ])->find($this->anakId);
            }
        }

        return array_merge([
            'startDate'           => $startDate,
            'endDate'             => $endDate,
            'periodeLabel'        => Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY'),
            'daftarIbu'           => $daftarIbu,
            'daftarAnak'          => $daftarAnak,
            'dataIbu'             => $dataIbu,
            'dataAnak'            => $dataAnak,
        ], $rekap);
    }
};
?>
<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-xl text-slate-900">Laporan</h2>
            <p class="text-sm text-slate-500">Rekapitulasi kegiatan posyandu per periode</p>
        </div>
        
        @php
            $exportUrl = route('laporan.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'tipe' => $tipeLaporan]);
            if ($tipeLaporan === 'ibu' && $ibuId) $exportUrl .= '&id=' . $ibuId;
            elseif ($tipeLaporan === 'anak' && $anakId) $exportUrl .= '&id=' . $anakId;
        @endphp

        <a href="{{ $exportUrl }}"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 h-10 px-5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97] w-full sm:w-auto {{ ($tipeLaporan === 'ibu' && !$ibuId) || ($tipeLaporan === 'anak' && !$anakId) ? 'opacity-50 pointer-events-none' : '' }}">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export PDF
        </a>
    </div>

    {{-- Tabs / Tipe Laporan --}}
    <div class="flex border-b border-slate-200 mb-6 overflow-x-auto whitespace-nowrap scrollbar-none">
        <button wire:click="$set('tipeLaporan', 'periode')" class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors shrink-0 {{ $tipeLaporan === 'periode' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Rekapitulasi Periode</button>
        <button wire:click="$set('tipeLaporan', 'ibu')" class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors shrink-0 {{ $tipeLaporan === 'ibu' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Laporan per Ibu</button>
        <button wire:click="$set('tipeLaporan', 'anak')" class="px-5 py-3 text-sm font-semibold border-b-2 transition-colors shrink-0 {{ $tipeLaporan === 'anak' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Laporan per Anak</button>
    </div>

    {{-- Filter Periode --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end">
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal Awal</label>
                <input type="date" wire:model.live="startDate" class="w-full sm:w-auto h-10 px-3 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
                <input type="date" wire:model.live="endDate" class="w-full sm:w-auto h-10 px-3 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none">
            </div>

            @if($tipeLaporan === 'ibu')
            <div class="flex-1 w-full max-w-sm ml-0 sm:ml-4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Ibu</label>
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchIbu.focus())" 
                         class="w-full h-10 px-3 border-[1.5px] border-slate-300 rounded-[10px] text-sm flex items-center justify-between bg-white cursor-pointer transition-all hover:border-slate-400">
                        @if($ibuId)
                            @php $selectedIbu = collect($daftarIbu)->firstWhere('id', $ibuId) ?? \App\Models\Ibu::find($ibuId); @endphp
                            @if($selectedIbu)
                                <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                    <span class="text-slate-800 font-medium truncate">{{ $selectedIbu->nama }}</span>
                                    <span class="text-slate-400 text-xs shrink-0">· NIK: {{ $selectedIbu->nik ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    <button type="button" wire:click.stop="resetIbu" class="p-1 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-md transition-colors" title="Hapus pilihan">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            @else
                                <span class="text-slate-400">Pilih Ibu...</span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            @endif
                        @else
                            <span class="text-slate-400">Cari nama ibu...</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        @endif
                    </div>
                    <div x-show="open" style="display: none;" class="relative">
                        <div class="relative">
                            <input x-ref="searchIbu" 
                                   wire:model.live.debounce.300ms="searchIbu" 
                                   type="text"
                                   @keydown.enter.prevent
                                   @keydown.escape="open = false"
                                   class="w-full h-10 pl-8 pr-8 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                                   placeholder="Cari nama ibu...">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <button type="button" @click="open = false" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <div class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto divide-y divide-slate-100">
                            @forelse($daftarIbu as $ibu)
                                <button type="button" 
                                        wire:key="ibu-opt-{{ $ibu->id }}"
                                        wire:click="selectIbu({{ $ibu->id }})" 
                                        @click="open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 hover:bg-primary-50 text-left transition-colors {{ $ibuId == $ibu->id ? 'bg-primary-50/80 text-primary-700' : 'text-slate-700' }}">
                                    <span class="text-sm font-medium text-slate-800">{{ $ibu->nama }}</span>
                                    <span class="text-xs text-slate-400 ml-auto">NIK: {{ $ibu->nik ?? '-' }}</span>
                                    @if($ibuId == $ibu->id)
                                        <svg class="w-4 h-4 text-primary-600 shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </button>
                            @empty
                                <p class="px-3 py-4 text-sm text-slate-400 text-center">Tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @elseif($tipeLaporan === 'anak')
            <div class="flex-1 w-full max-w-sm ml-0 sm:ml-4">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Anak</label>
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchAnak.focus())" 
                         class="w-full h-10 px-3 border-[1.5px] border-slate-300 rounded-[10px] text-sm flex items-center justify-between bg-white cursor-pointer transition-all hover:border-slate-400">
                        @if($anakId)
                            @php $selectedAnak = collect($daftarAnak)->firstWhere('id', $anakId) ?? \App\Models\Anak::find($anakId); @endphp
                            @if($selectedAnak)
                                <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                    <span class="text-slate-800 font-medium truncate">{{ $selectedAnak->nama }}</span>
                                    <span class="text-slate-400 text-xs shrink-0">({{ $selectedAnak->usia }})</span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    <button type="button" wire:click.stop="resetAnak" class="p-1 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-md transition-colors" title="Hapus pilihan">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            @else
                                <span class="text-slate-400">Pilih Anak...</span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            @endif
                        @else
                            <span class="text-slate-400">Cari nama anak...</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        @endif
                    </div>
                    <div x-show="open" style="display: none;" class="relative">
                        <div class="relative">
                            <input x-ref="searchAnak" 
                                   wire:model.live.debounce.300ms="searchAnak" 
                                   type="text"
                                   @keydown.enter.prevent
                                   @keydown.escape="open = false"
                                   class="w-full h-10 pl-8 pr-8 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                                   placeholder="Cari nama anak...">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <button type="button" @click="open = false" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <div class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto divide-y divide-slate-100">
                            @forelse($daftarAnak as $anak)
                                <button type="button" 
                                        wire:key="anak-opt-{{ $anak->id }}"
                                        wire:click="selectAnak({{ $anak->id }})" 
                                        @click="open = false" 
                                        class="w-full flex items-center justify-between px-3 py-2 hover:bg-primary-50 text-left transition-colors {{ $anakId == $anak->id ? 'bg-primary-50/80 text-primary-700' : 'text-slate-700' }}">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-slate-800">{{ $anak->nama }}</span>
                                        <span class="text-xs text-slate-400">Ibu: {{ $anak->ibu->nama ?? '-' }}</span>
                                    </div>
                                    @if($anakId == $anak->id)
                                        <svg class="w-4 h-4 text-primary-600 shrink-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </button>
                            @empty
                                <p class="px-3 py-4 text-sm text-slate-400 text-center">Tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="self-start sm:self-end sm:ml-auto pb-1 sm:pb-2">
                <p class="text-sm font-semibold text-primary-700">{{ $periodeLabel }}</p>
            </div>
        </div>
    </div>

    @if($tipeLaporan === 'periode')
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Ibu</p>
                <p class="text-3xl font-bold text-primary-600">{{ $totalIbu ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Anak</p>
                <p class="text-3xl font-bold text-secondary-500">{{ $totalAnak ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ditimbang</p>
                <p class="text-3xl font-bold text-emerald-600">{{ $totalPenimbangan ?? 0 }}</p>
                <p class="text-xs text-slate-400 mt-1">dari {{ $totalAnak ?? 0 }} anak</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Coverage</p>
                <p class="text-3xl font-bold text-slate-700">{{ ($totalAnak ?? 0) > 0 ? round(($totalPenimbangan ?? 0) / ($totalAnak ?? 1) * 100) : 0 }}%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Status Gizi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h3 class="font-heading font-semibold text-slate-800 mb-4">Status Gizi (BB/U)</h3>
                <div class="space-y-3">
                    @foreach(['buruk' => ['Gizi Buruk','bg-red-500'], 'kurang' => ['Gizi Kurang','bg-amber-400'], 'baik' => ['Gizi Baik','bg-green-500'], 'lebih' => ['Gizi Lebih','bg-blue-500']] as $key => [$label, $color])
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full {{ $color }}"></div>
                            <span class="text-sm text-slate-600 flex-1">{{ $label }}</span>
                            <span class="text-sm font-bold text-slate-800">{{ $statusGizi[$key] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Imunisasi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h3 class="font-heading font-semibold text-slate-800 mb-4">Imunisasi Periode Ini</h3>
                @if(isset($imunisasiPeriode) && $imunisasiPeriode->count() > 0)
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($imunisasiPeriode as $nama => $items)
                            <div class="flex items-center justify-between py-1.5 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-600">{{ $nama }}</span>
                                <span class="text-sm font-bold text-slate-800">{{ $items->count() }}×</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 py-4 text-center">Tidak ada data</p>
                @endif
            </div>

            {{-- Vitamin --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h3 class="font-heading font-semibold text-slate-800 mb-4">Vitamin Periode Ini</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                        <span class="text-sm text-slate-600 flex-1">Kapsul Biru (6-11 bln)</span>
                        <span class="text-sm font-bold text-slate-800">{{ $vitaminBiru ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                        <span class="text-sm text-slate-600 flex-1">Kapsul Merah (12-59 bln)</span>
                        <span class="text-sm font-bold text-slate-800">{{ $vitaminMerah ?? 0 }}</span>
                    </div>
                    <div class="pt-2 mt-2 border-t border-slate-100 flex justify-between">
                        <span class="text-sm font-semibold text-slate-700">Total</span>
                        <span class="text-sm font-bold text-slate-800">{{ ($vitaminBiru ?? 0) + ($vitaminMerah ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @elseif($tipeLaporan === 'ibu')
        @if($dataIbu)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-heading font-semibold text-slate-800 text-lg">{{ $dataIbu->nama }}</h3>
                    <p class="text-sm text-slate-500 mt-1">NIK: {{ $dataIbu->nik ?? '-' }} &bull; Alamat: {{ $dataIbu->alamat ?? '-' }}</p>
                </div>
                <div class="p-5">
                    <h4 class="font-semibold text-slate-700 mb-4 text-sm">Daftar Anak & Status Terakhir (Periode Ini)</h4>
                    @if($dataIbu->anak->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="border-b-2 border-slate-100">
                                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Nama Anak</th>
                                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase">L/P</th>
                                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Usia</th>
                                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Status Gizi (BB/U)</th>
                                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Imunisasi Diberikan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($dataIbu->anak as $anak)
                                        @php
                                            $penimbanganTerakhir = $anak->penimbangan->first();
                                            $statusBadge = '-';
                                            if ($penimbanganTerakhir) {
                                                $s = $penimbanganTerakhir->status_bbu;
                                                if ($s == 'buruk') $statusBadge = '<span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-full text-xs font-medium">Buruk</span>';
                                                elseif ($s == 'kurang') $statusBadge = '<span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-medium">Kurang</span>';
                                                elseif ($s == 'baik') $statusBadge = '<span class="px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">Baik</span>';
                                                elseif ($s == 'lebih') $statusBadge = '<span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium">Lebih</span>';
                                            }
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3 px-4 font-medium text-slate-800 text-sm">{{ $anak->nama }}</td>
                                            <td class="py-3 px-4 text-slate-600 text-sm">{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                            <td class="py-3 px-4 text-slate-600 text-sm">{{ $anak->usia }}</td>
                                            <td class="py-3 px-4">{!! $statusBadge !!}</td>
                                            <td class="py-3 px-4 text-slate-600 text-sm">{{ $anak->imunisasi->count() > 0 ? $anak->imunisasi->count() . ' kali' : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 py-4 text-center">Belum ada data anak atau riwayat pada periode ini.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 border-dashed p-10 text-center">
                <p class="text-slate-500">Pilih Ibu dari filter di atas untuk melihat laporan detail.</p>
            </div>
        @endif
    @elseif($tipeLaporan === 'anak')
        @if($dataAnak)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h3 class="font-heading font-semibold text-slate-800 text-lg">{{ $dataAnak->nama }}</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $dataAnak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }} &bull; Usia: {{ $dataAnak->usia }} &bull; Ibu: {{ $dataAnak->ibu->nama ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <livewire:penimbangan.grafik-kms :anak-id="$dataAnak->id" :hide-header="true" :hide-table="true" wire:key="kms-{{ $dataAnak->id }}" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Penimbangan --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h4 class="font-semibold text-slate-800 mb-4 text-sm border-b border-slate-100 pb-2">Riwayat Penimbangan (Periode Ini)</h4>
                    @if($dataAnak->penimbangan->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[480px]">
                                <thead>
                                    <tr class="border-b-2 border-slate-100">
                                        <th class="py-2 px-1 text-xs font-semibold text-slate-500">Tanggal</th>
                                        <th class="py-2 px-1 text-xs font-semibold text-slate-500">Usia</th>
                                        <th class="py-2 px-1 text-xs font-semibold text-slate-500">BB (kg)</th>
                                        <th class="py-2 px-1 text-xs font-semibold text-slate-500">TB (cm)</th>
                                        <th class="py-2 px-1 text-xs font-semibold text-slate-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($dataAnak->penimbangan as $p)
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-2 px-1 text-sm text-slate-700">{{ $p->tanggal_pelayanan->format('d/m/Y') }}</td>
                                            <td class="py-2 px-1 text-sm text-slate-600">{{ $p->usia_saat_ukur ?? '-' }} bln</td>
                                            <td class="py-2 px-1 text-sm font-medium {{ $p->status_bbu == 'buruk' ? 'text-red-600' : ($p->status_bbu == 'kurang' ? 'text-amber-600' : 'text-slate-700') }}">{{ $p->berat_badan }}</td>
                                            <td class="py-2 px-1 text-sm text-slate-700">{{ $p->tinggi_badan }}</td>
                                            <td class="py-2 px-1 text-sm text-slate-700 capitalize">{{ $p->status_bbu }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 py-4 text-center">Belum ada riwayat penimbangan.</p>
                    @endif
                </div>

                <div class="space-y-6">
                    {{-- Imunisasi --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <h4 class="font-semibold text-slate-800 mb-4 text-sm border-b border-slate-100 pb-2">Riwayat Imunisasi (Periode Ini)</h4>
                        @if($dataAnak->imunisasi->count() > 0)
                            <ul class="space-y-3">
                                @foreach($dataAnak->imunisasi as $im)
                                    <li class="flex justify-between items-center text-sm p-2 hover:bg-slate-50 rounded-lg">
                                        <span class="text-slate-700 font-medium">{{ $im->jenisImunisasi->nama ?? '-' }}</span>
                                        <span class="text-slate-500 text-xs">{{ $im->tanggal_imunisasi->format('d/m/Y') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 py-4 text-center">Belum ada imunisasi.</p>
                        @endif
                    </div>

                    {{-- Vitamin --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <h4 class="font-semibold text-slate-800 mb-4 text-sm border-b border-slate-100 pb-2">Riwayat Vitamin (Periode Ini)</h4>
                        @if($dataAnak->vitamin->count() > 0)
                            <ul class="space-y-3">
                                @foreach($dataAnak->vitamin as $vit)
                                    <li class="flex justify-between items-center text-sm p-2 hover:bg-slate-50 rounded-lg">
                                        <span class="text-slate-700 font-medium">{{ $vit->jenis_vitamin == 'kapsul_biru' ? 'Kapsul Biru' : 'Kapsul Merah' }}</span>
                                        <span class="text-slate-500 text-xs">{{ $vit->tanggal_pemberian->format('d/m/Y') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 py-4 text-center">Belum ada pemberian vitamin.</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 border-dashed p-10 text-center">
                <p class="text-slate-500">Pilih Anak dari filter di atas untuk melihat laporan detail.</p>
            </div>
        @endif
    @endif
</div>
