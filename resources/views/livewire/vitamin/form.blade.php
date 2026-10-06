<?php

use App\Models\Anak;
use App\Models\Vitamin;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $vitaminId = null;
    public string $anak_id = '';
    public string $tanggal_pemberian = '';
    public string $jenis_vitamin = '';
    public string $keterangan = '';
    public string $searchAnak = '';

    public ?Anak $anakDipilih = null;
    public string $warningVitamin = '';

    public function mount(?int $id = null): void
    {
        $this->tanggal_pemberian = now()->format('Y-m-d');

        if ($id) {
            $vitamin = Vitamin::findOrFail($id);
            $this->vitaminId = $vitamin->id;
            $this->anak_id = (string) $vitamin->anak_id;
            $this->tanggal_pemberian = $vitamin->tanggal_pemberian->format('Y-m-d');
            $this->jenis_vitamin = $vitamin->jenis_vitamin;
            $this->keterangan = $vitamin->keterangan ?? '';
            $this->loadAnak();
        }
    }

    public function selectAnak(int $id): void
    {
        $this->anak_id = (string) $id;
        $this->searchAnak = '';
        $this->loadAnak();
    }

    public function resetAnak(): void
    {
        $this->anak_id = '';
        $this->searchAnak = '';
        $this->anakDipilih = null;
        $this->jenis_vitamin = '';
        $this->warningVitamin = '';
    }

    public function updatedAnakId(): void
    {
        $this->loadAnak();
    }

    private function loadAnak(): void
    {
        if ($this->anak_id) {
            $this->anakDipilih = Anak::find($this->anak_id);
            if ($this->anakDipilih) {
                $usiaBulan = $this->anakDipilih->usia_in_bulan;
                if ($usiaBulan >= 6 && $usiaBulan <= 11) {
                    $this->jenis_vitamin = 'kapsul_biru';
                    $this->warningVitamin = '';
                } elseif ($usiaBulan >= 12 && $usiaBulan <= 59) {
                    $this->jenis_vitamin = 'kapsul_merah';
                    $this->warningVitamin = '';
                } else {
                    $this->jenis_vitamin = '';
                    $this->warningVitamin = "Usia anak {$this->anakDipilih->usia} tidak termasuk dalam rentang pemberian vitamin A (6-59 bulan).";
                }
            }
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'anak_id'          => 'required|exists:anak,id',
            'tanggal_pemberian'=> 'required|date',
            'jenis_vitamin'    => 'required|in:kapsul_biru,kapsul_merah',
            'keterangan'       => 'nullable|string',
        ], [
            'anak_id.required'           => 'Anak wajib dipilih.',
            'tanggal_pemberian.required' => 'Tanggal wajib diisi.',
            'jenis_vitamin.required'     => 'Jenis vitamin wajib dipilih.',
        ]);

        if ($this->vitaminId) {
            Vitamin::findOrFail($this->vitaminId)->update($validated);
            session()->flash('success', 'Data vitamin berhasil diperbarui.');
        } else {
            Vitamin::create($validated);
            session()->flash('success', 'Data vitamin berhasil disimpan.');
        }

        $this->redirect(route('vitamin.index'));
    }

    public function with(): array
    {
        $daftarAnak = Anak::with('ibu')
            ->when($this->searchAnak, fn ($q) => $q->where('nama', 'like', "%{$this->searchAnak}%"))
            ->orderBy('nama')->limit(20)->get();

        $selectedAnak = $this->anakDipilih ?? ($this->anak_id ? Anak::with('ibu')->find($this->anak_id) : null);

        return compact('daftarAnak', 'selectedAnak');
    }

    public function title(): string
    {
        return $this->vitaminId ? 'Edit Vitamin' : 'Tambah Vitamin';
    }
};
?>
<div>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('vitamin.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h2 class="font-heading font-bold text-xl text-slate-900">{{ $vitaminId ? 'Edit Vitamin' : 'Tambah Vitamin' }}</h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-6">
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

                {{-- Warning usia --}}
                @if($warningVitamin)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700">
                        <svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        {{ $warningVitamin }}
                    </div>
                @endif

                {{-- Jenis Vitamin (auto-filled, tapi bisa diubah) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Vitamin <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 px-4 py-2.5 border-[1.5px] rounded-[10px] cursor-pointer transition-all {{ $jenis_vitamin === 'kapsul_biru' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" wire:model="jenis_vitamin" value="kapsul_biru" class="text-blue-500">
                            <span class="text-sm font-medium">Kapsul Biru (6-11 bln)</span>
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2.5 border-[1.5px] rounded-[10px] cursor-pointer transition-all {{ $jenis_vitamin === 'kapsul_merah' ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" wire:model="jenis_vitamin" value="kapsul_merah" class="text-red-500">
                            <span class="text-sm font-medium">Kapsul Merah (12-59 bln)</span>
                        </label>
                    </div>
                    @error('jenis_vitamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label for="tanggal_pemberian" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Pemberian <span class="text-red-500">*</span></label>
                    <input wire:model="tanggal_pemberian" type="date" id="tanggal_pemberian"
                           class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all">
                    @error('tanggal_pemberian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Keterangan --}}
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan <span class="text-slate-400 text-xs font-normal">opsional</span></label>
                    <textarea wire:model="keterangan" id="keterangan" rows="2"
                              class="w-full px-3.5 py-2.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all resize-none"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97] w-full sm:w-auto">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Simpan
                    </button>
                    <a href="{{ route('vitamin.index') }}" class="inline-flex items-center justify-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all w-full sm:w-auto">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
