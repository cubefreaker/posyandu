<?php

use App\Models\Anak;
use App\Models\Ibu;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $anakId = null;
    public string $ibu_id = '';
    public string $nama = '';
    public string $tanggal_lahir = '';
    public string $jenis_kelamin = '';
    public string $searchIbu = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $anak = Anak::findOrFail($id);
            $this->anakId = $anak->id;
            $this->ibu_id = (string) $anak->ibu_id;
            $this->nama = $anak->nama;
            $this->tanggal_lahir = $anak->tanggal_lahir->format('Y-m-d');
            $this->jenis_kelamin = $anak->jenis_kelamin;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'ibu_id' => 'required|exists:ibu,id',
            'nama' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
        ], [
            'ibu_id.required' => 'Ibu wajib dipilih.',
            'nama.required' => 'Nama anak wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        if ($this->anakId) {
            Anak::findOrFail($this->anakId)->update($validated);
            session()->flash('success', 'Data anak berhasil diperbarui.');
        } else {
            Anak::create($validated);
            session()->flash('success', 'Data anak berhasil ditambahkan.');
        }
        $this->redirect(route('data-anak.index'));
    }

    public function selectIbu(int $id): void
    {
        $this->ibu_id = (string) $id;
        $this->searchIbu = '';
    }

    public function resetIbu(): void
    {
        $this->ibu_id = '';
        $this->searchIbu = '';
    }

    public function with(): array
    {
        $daftarIbu = Ibu::when($this->searchIbu, fn ($q) => $q->where('nama', 'like', "%{$this->searchIbu}%")->orWhere('nik', 'like', "%{$this->searchIbu}%"))
            ->orderBy('nama')->limit(20)->get();
        $selectedIbu = $this->ibu_id ? Ibu::find($this->ibu_id) : null;
        return [
            'daftarIbu' => $daftarIbu,
            'selectedIbu' => $selectedIbu,
        ];
    }
    
    public function title(): string
    {
        return $this->anakId ? 'Edit Data Anak' : 'Tambah Data Anak';
    }
};
?>
<div>
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('data-anak.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h2 class="font-heading font-bold text-xl text-slate-900">{{ $anakId ? 'Edit Data Anak' : 'Tambah Data Anak' }}</h2>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-5">
                {{-- Pilih Ibu --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ibu / Wali <span class="text-red-500">*</span></label>
                    
                    {{-- Selected Value Display --}}
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" 
                         class="w-full h-11 px-3.5 border-[1.5px] {{ $errors->has('ibu_id') ? 'border-red-400' : 'border-slate-300 hover:border-slate-400' }} rounded-[10px] text-sm flex items-center justify-between bg-white cursor-pointer transition-all">
                        @if($selectedIbu)
                            <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                <span class="text-slate-800 font-medium truncate">{{ $selectedIbu->nama }}</span>
                                <span class="text-slate-400 font-mono text-xs shrink-0">· NIK: {{ $selectedIbu->nik ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                <button type="button" wire:click.stop="resetIbu" class="p-1 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-md transition-colors" title="Hapus pilihan">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                                <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        @else
                            <span class="text-slate-400">Cari nama atau NIK ibu...</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        @endif
                    </div>

                    {{-- Search Input and Dropdown --}}
                    <div x-show="open" style="display: none;" class="relative">
                        <div class="relative">
                            <input x-ref="searchInput" 
                                   wire:model.live.debounce.300ms="searchIbu" 
                                   type="text"
                                   @keydown.enter.prevent
                                   @keydown.escape="open = false"
                                   class="w-full h-11 pl-9 pr-9 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                                   placeholder="Ketik nama atau NIK ibu...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <button type="button" @click="open = false" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <div class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-52 overflow-y-auto divide-y divide-slate-100">
                            @forelse($daftarIbu as $ibu)
                                <button type="button" 
                                        wire:key="ibu-option-{{ $ibu->id }}"
                                        wire:click="selectIbu({{ $ibu->id }})" 
                                        @click="open = false" 
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-primary-50 text-left transition-colors {{ $ibu_id == $ibu->id ? 'bg-primary-50/80 text-primary-700' : 'text-slate-700' }}">
                                    <div>
                                        <span class="text-sm font-medium text-slate-800">{{ $ibu->nama }}</span>
                                        <span class="text-xs text-slate-400 ml-2 font-mono">NIK: {{ $ibu->nik ?? '-' }}</span>
                                    </div>
                                    @if($ibu_id == $ibu->id)
                                        <svg class="w-4 h-4 text-primary-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </button>
                            @empty
                                <p class="px-3 py-4 text-sm text-slate-400 text-center">Tidak ada data ibu ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                    @error('ibu_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Nama Anak --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Anak <span class="text-red-500">*</span></label>
                    <input wire:model="nama" type="text" id="nama"
                           class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none {{ $errors->has('nama') ? 'border-red-400' : 'border-slate-300 focus:border-primary-500' }} focus:ring-[3px] focus:ring-primary-500/20"
                           placeholder="Masukkan nama anak">
                    @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input wire:model="tanggal_lahir" type="date" id="tanggal_lahir"
                           class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none {{ $errors->has('tanggal_lahir') ? 'border-red-400' : 'border-slate-300 focus:border-primary-500' }} focus:ring-[3px] focus:ring-primary-500/20">
                    @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 px-4 py-2.5 border-[1.5px] rounded-[10px] cursor-pointer transition-all {{ $jenis_kelamin === 'L' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" wire:model="jenis_kelamin" value="L" class="text-primary-500 focus:ring-primary-500/20">
                            <span class="text-sm font-medium">Laki-laki</span>
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2.5 border-[1.5px] rounded-[10px] cursor-pointer transition-all {{ $jenis_kelamin === 'P' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" wire:model="jenis_kelamin" value="P" class="text-primary-500 focus:ring-primary-500/20">
                            <span class="text-sm font-medium">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan
                    </button>
                    <a href="{{ route('data-anak.index') }}" class="inline-flex items-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
