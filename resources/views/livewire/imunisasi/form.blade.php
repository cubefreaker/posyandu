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

    public function with(): array
    {
        $daftarAnak = Anak::with('ibu')
            ->when($this->searchAnak, fn ($q) => $q->where('nama', 'like', "%{$this->searchAnak}%"))
            ->orderBy('nama')->limit(20)->get();

        $jenisImunisasi = JenisImunisasi::orderBy('urutan')->get();

        return compact('daftarAnak', 'jenisImunisasi');
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-5">
                {{-- Pilih Anak --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Anak <span class="text-red-500">*</span></label>
                    
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" 
                         class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm flex items-center bg-white cursor-text transition-all hover:border-slate-400">
                        @if($anak_id)
                            @php $selectedAnak = $daftarAnak->firstWhere('id', $anak_id) ?? \App\Models\Anak::find($anak_id); @endphp
                            @if($selectedAnak)
                                <span class="text-slate-800 font-medium">{{ $selectedAnak->nama }}</span>
                                <span class="text-slate-400 ml-2 text-xs">· {{ $selectedAnak->usia ?? '' }}</span>
                            @else
                                <span class="text-slate-400">Pilih Anak...</span>
                            @endif
                        @else
                            <span class="text-slate-400">Cari nama anak...</span>
                        @endif
                    </div>

                    <div x-show="open" style="display: none;" class="relative">
                        <input x-ref="searchInput" wire:model.live.debounce.300ms="searchAnak" type="text"
                               class="w-full h-11 px-3.5 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="Cari nama anak...">
                        <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                            @forelse($daftarAnak as $anak)
                                <label @click="open = false" class="flex items-center gap-3 px-3 py-2.5 hover:bg-primary-50 cursor-pointer transition-colors {{ $anak_id == $anak->id ? 'bg-primary-50' : '' }}">
                                    <input type="radio" wire:model="anak_id" value="{{ $anak->id }}" class="hidden">
                                    <span class="text-sm font-medium text-slate-800">{{ $anak->nama }}</span>
                                    <span class="text-xs text-slate-400">· {{ $anak->usia }}</span>
                                </label>
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

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Simpan
                    </button>
                    <a href="{{ route('imunisasi.index') }}" class="inline-flex items-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
