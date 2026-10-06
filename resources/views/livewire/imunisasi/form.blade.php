<?php

use App\Models\Anak;
use App\Models\Imunisasi;
use App\Models\JenisImunisasi;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $imunisasiId = null;
    public string $anak_id = '';
    public string $jenis_imunisasi_id = '';
    public string $tanggal_imunisasi = '';
    public string $keterangan = '';
    public string $searchAnak = '';

    public function mount(?int $id = null): void
    {
        $this->tanggal_imunisasi = now()->format('Y-m-d');

        if ($id) {
            $imunisasi = Imunisasi::findOrFail($id);
            $this->imunisasiId = $imunisasi->id;
            $this->anak_id = (string) $imunisasi->anak_id;
            $this->jenis_imunisasi_id = (string) $imunisasi->jenis_imunisasi_id;
            $this->tanggal_imunisasi = $imunisasi->tanggal_imunisasi->format('Y-m-d');
            $this->keterangan = $imunisasi->keterangan ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'anak_id'           => 'required|exists:anak,id',
            'jenis_imunisasi_id'=> 'required|exists:jenis_imunisasi,id',
            'tanggal_imunisasi' => 'required|date',
            'keterangan'        => 'nullable|string',
        ], [
            'anak_id.required'            => 'Anak wajib dipilih.',
            'jenis_imunisasi_id.required' => 'Jenis imunisasi wajib dipilih.',
            'tanggal_imunisasi.required'  => 'Tanggal imunisasi wajib diisi.',
        ]);

        // Validasi duplikasi
        if (!$this->imunisasiId) {
            $exists = Imunisasi::where('anak_id', $validated['anak_id'])
                ->where('jenis_imunisasi_id', $validated['jenis_imunisasi_id'])
                ->exists();

            if ($exists) {
                $this->addError('jenis_imunisasi_id', 'Imunisasi ini sudah pernah diberikan untuk anak ini.');
                return;
            }
        }

        if ($this->imunisasiId) {
            Imunisasi::findOrFail($this->imunisasiId)->update($validated);
            session()->flash('success', 'Data imunisasi berhasil diperbarui.');
        } else {
            Imunisasi::create($validated);
            session()->flash('success', 'Data imunisasi berhasil disimpan.');
        }

        $this->redirect(route('imunisasi.index'));
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

    public function with(): array
    {
        $daftarAnak = Anak::with('ibu')
            ->when($this->searchAnak, fn ($q) => $q->where('nama', 'like', "%{$this->searchAnak}%"))
            ->orderBy('nama')->limit(20)->get();

        $selectedAnak = $this->anak_id ? Anak::with('ibu')->find($this->anak_id) : null;
        $jenisImunisasi = JenisImunisasi::orderBy('urutan')->get();

        return compact('daftarAnak', 'selectedAnak', 'jenisImunisasi');
    }

    public function title(): string
    {
        return $this->imunisasiId ? 'Edit Imunisasi' : 'Tambah Imunisasi';
    }
};
?>
<div>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('imunisasi.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h2 class="font-heading font-bold text-xl text-slate-900">{{ $imunisasiId ? 'Edit Imunisasi' : 'Tambah Imunisasi' }}</h2>
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

                {{-- Jenis Imunisasi --}}
                <div>
                    <label for="jenis_imunisasi_id" class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Imunisasi <span class="text-red-500">*</span></label>
                    <select wire:model="jenis_imunisasi_id" id="jenis_imunisasi_id"
                            class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none {{ $errors->has('jenis_imunisasi_id') ? 'border-red-400' : 'border-slate-300 focus:border-primary-500' }} focus:ring-[3px] focus:ring-primary-500/20">
                        <option value="">— Pilih jenis imunisasi —</option>
                        @foreach($jenisImunisasi as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama }} ({{ $jenis->usia_pemberian }})</option>
                        @endforeach
                    </select>
                    @error('jenis_imunisasi_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label for="tanggal_imunisasi" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Imunisasi <span class="text-red-500">*</span></label>
                    <input wire:model="tanggal_imunisasi" type="date" id="tanggal_imunisasi"
                           class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all">
                    @error('tanggal_imunisasi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <a href="{{ route('imunisasi.index') }}" class="inline-flex items-center justify-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all w-full sm:w-auto">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
