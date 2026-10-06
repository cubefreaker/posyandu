<?php

use App\Models\Anak;
use App\Models\Penimbangan;
use App\Services\StatusGiziService;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $penimbanganId = null;
    public string $anak_id = '';
    public string $tanggal_pelayanan = '';
    public string $berat_badan = '';
    public string $tinggi_badan = '';
    public string $lingkar_kepala = '';
    public string $lila = '';
    public string $searchAnak = '';

    public function mount(?int $id = null): void
    {
        $this->tanggal_pelayanan = now()->format('Y-m-d');

        if (request()->has('anak_id')) {
            $this->anak_id = request()->query('anak_id');
        }

        if ($id) {
            $p = Penimbangan::findOrFail($id);
            $this->penimbanganId = $p->id;
            $this->anak_id = (string) $p->anak_id;
            $this->tanggal_pelayanan = $p->tanggal_pelayanan->format('Y-m-d');
            $this->berat_badan = (string) $p->berat_badan;
            $this->tinggi_badan = (string) $p->tinggi_badan;
            $this->lingkar_kepala = $p->lingkar_kepala ? (string) $p->lingkar_kepala : '';
            $this->lila = $p->lila ? (string) $p->lila : '';
        }
    }

    public function selectAnak(int $id): void
    {
        $this->anak_id = (string) $id;
        $this->searchAnak = '';
    }

    public function resetAnak(): void
    {
        $this->anak_id = '';
        $this->searchAnak = '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'anak_id'          => 'required|exists:anak,id',
            'tanggal_pelayanan'=> 'required|date',
            'berat_badan'      => 'required|numeric|min:0.1|max:99',
            'tinggi_badan'     => 'required|numeric|min:10|max:200',
            'lingkar_kepala'   => 'nullable|numeric|min:10|max:100',
            'lila'             => 'nullable|numeric|min:5|max:50',
        ], [
            'anak_id.required'          => 'Anak wajib dipilih.',
            'tanggal_pelayanan.required'=> 'Tanggal pelayanan wajib diisi.',
            'berat_badan.required'      => 'Berat badan wajib diisi.',
            'tinggi_badan.required'     => 'Tinggi badan wajib diisi.',
        ]);

        // Kalkulasi z-score dan status gizi
        $anak = Anak::findOrFail($validated['anak_id']);
        $usiaInBulan = $anak->tanggal_lahir->diffInMonths(now());

        $statusGizi = StatusGiziService::hitung(
            $usiaInBulan,
            $anak->jenis_kelamin,
            (float) $validated['berat_badan'],
            (float) $validated['tinggi_badan']
        );

        $data = array_merge($validated, $statusGizi, [
            'lingkar_kepala' => $validated['lingkar_kepala'] ?: null,
            'lila'           => $validated['lila'] ?: null,
        ]);

        if ($this->penimbanganId) {
            Penimbangan::findOrFail($this->penimbanganId)->update($data);
            session()->flash('success', 'Data penimbangan berhasil diperbarui.');
        } else {
            Penimbangan::create($data);
            session()->flash('success', 'Data penimbangan berhasil disimpan.');
        }

        $this->redirect(route('penimbangan.index'));
    }

    public function with(): array
    {
        $daftarAnak = Anak::with('ibu')
            ->when($this->searchAnak, fn ($q) => $q->where('nama', 'like', "%{$this->searchAnak}%"))
            ->orderBy('nama')
            ->limit(20)
            ->get();

        $selectedAnak = $this->anak_id ? Anak::with('ibu')->find($this->anak_id) : null;

        return [
            'daftarAnak' => $daftarAnak,
            'selectedAnak' => $selectedAnak,
        ];
    }
    
    public function title(): string
    {
        return $this->penimbanganId ? 'Edit Penimbangan' : 'Tambah Penimbangan';
    }
};
?>
<div>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('penimbangan.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h2 class="font-heading font-bold text-xl text-slate-900">{{ $penimbanganId ? 'Edit Penimbangan' : 'Tambah Penimbangan' }}</h2>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-5">
                {{-- Pilih Anak --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Anak <span class="text-red-500">*</span></label>
                    
                    {{-- Tampilan saat tertutup (selected value / placeholder) --}}
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" 
                         class="w-full h-11 px-3.5 border-[1.5px] {{ $errors->has('anak_id') ? 'border-red-400' : 'border-slate-300 hover:border-slate-400' }} rounded-[10px] text-sm flex items-center justify-between bg-white cursor-pointer transition-all">
                        @if($selectedAnak)
                            <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                <span class="text-slate-800 font-medium truncate">{{ $selectedAnak->nama }}</span>
                                <span class="text-slate-400 text-xs shrink-0">· {{ $selectedAnak->usia ?? '' }}</span>
                                @if($selectedAnak->ibu)
                                    <span class="text-slate-400 text-xs truncate">· Ibu: {{ $selectedAnak->ibu->nama }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                <button type="button" wire:click.stop="resetAnak" class="p-1 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-md transition-colors" title="Hapus pilihan">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                                <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        @else
                            <span class="text-slate-400">Cari nama anak...</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        @endif
                    </div>

                    {{-- Tampilan saat terbuka (input search + list dropdown) --}}
                    <div x-show="open" style="display: none;" class="relative">
                        <div class="relative">
                            <input x-ref="searchInput" 
                                   wire:model.live.debounce.300ms="searchAnak" 
                                   type="text"
                                   @keydown.enter.prevent
                                   @keydown.escape="open = false"
                                   class="w-full h-11 pl-9 pr-9 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                                   placeholder="Ketik untuk mencari nama anak...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <button type="button" @click="open = false" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <div class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-52 overflow-y-auto divide-y divide-slate-100">
                            @forelse($daftarAnak as $anak)
                                <button type="button" 
                                        wire:key="anak-option-{{ $anak->id }}"
                                        wire:click="selectAnak({{ $anak->id }})" 
                                        @click="open = false" 
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-primary-50 text-left transition-colors {{ $anak_id == $anak->id ? 'bg-primary-50/80 text-primary-700' : 'text-slate-700' }}">
                                    <div>
                                        <span class="text-sm font-medium text-slate-800">{{ $anak->nama }}</span>
                                        <span class="text-xs text-slate-400 ml-2">· {{ $anak->usia }}</span>
                                        <span class="block text-xs text-slate-400">Ibu: {{ $anak->ibu->nama ?? '-' }}</span>
                                    </div>
                                    @if($anak_id == $anak->id)
                                        <svg class="w-4 h-4 text-primary-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </button>
                            @empty
                                <p class="px-3 py-4 text-sm text-slate-400 text-center">Tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                    @error('anak_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal Pelayanan --}}
                <div>
                    <label for="tanggal_pelayanan" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Pelayanan <span class="text-red-500">*</span></label>
                    <input wire:model="tanggal_pelayanan" type="date" id="tanggal_pelayanan"
                           class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all">
                    @error('tanggal_pelayanan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- BB & TB --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="berat_badan" class="block text-sm font-medium text-slate-700 mb-1.5">Berat Badan (kg) <span class="text-red-500">*</span></label>
                        <input wire:model="berat_badan" type="number" id="berat_badan" step="0.1" min="0.1"
                               class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all {{ $errors->has('berat_badan') ? 'border-red-400' : 'border-slate-300' }}"
                               placeholder="cth: 8.5">
                        @error('berat_badan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tinggi_badan" class="block text-sm font-medium text-slate-700 mb-1.5">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                        <input wire:model="tinggi_badan" type="number" id="tinggi_badan" step="0.1" min="10"
                               class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all {{ $errors->has('tinggi_badan') ? 'border-red-400' : 'border-slate-300' }}"
                               placeholder="cth: 72.5">
                        @error('tinggi_badan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- LK & LILA (opsional) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="lingkar_kepala" class="block text-sm font-medium text-slate-700 mb-1.5">Lingkar Kepala (cm) <span class="text-slate-400 text-xs font-normal">opsional</span></label>
                        <input wire:model="lingkar_kepala" type="number" id="lingkar_kepala" step="0.1"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="cth: 45.0">
                    </div>
                    <div>
                        <label for="lila" class="block text-sm font-medium text-slate-700 mb-1.5">LILA (cm) <span class="text-slate-400 text-xs font-normal">opsional</span></label>
                        <input wire:model="lila" type="number" id="lila" step="0.1"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="cth: 14.5">
                    </div>
                </div>

                <div class="bg-primary-50 border border-primary-100 rounded-xl px-4 py-3 text-sm text-primary-700">
                    <svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Status gizi (BB/U, TB/U, BB/TB) akan dihitung otomatis berdasarkan standar Kemenkes.
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan & Hitung Status
                    </button>
                    <a href="{{ route('penimbangan.index') }}" class="inline-flex items-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
