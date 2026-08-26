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

    public function with(): array
    {
        $daftarIbu = Ibu::when($this->searchIbu, fn ($q) => $q->where('nama', 'like', "%{$this->searchIbu}%")->orWhere('nik', 'like', "%{$this->searchIbu}%"))
            ->orderBy('nama')->limit(20)->get();
        return ['daftarIbu' => $daftarIbu];
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
                         class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm flex items-center bg-white cursor-text transition-all hover:border-slate-400">
                        @if($ibu_id)
                            @php $selectedIbu = $daftarIbu->firstWhere('id', $ibu_id) ?? \App\Models\Ibu::find($ibu_id); @endphp
                            @if($selectedIbu)
                                <span class="text-slate-800 font-medium">{{ $selectedIbu->nama }}</span>
                                <span class="text-slate-400 ml-2 font-mono text-xs">{{ $selectedIbu->nik }}</span>
                            @else
                                <span class="text-slate-400">Pilih Ibu...</span>
                            @endif
                        @else
                            <span class="text-slate-400">Cari nama atau NIK ibu...</span>
                        @endif
                    </div>

                    {{-- Search Input and Dropdown --}}
                    <div x-show="open" style="display: none;" class="relative">
                        <input x-ref="searchInput" wire:model.live.debounce.300ms="searchIbu" type="text"
                               class="w-full h-11 px-3.5 border-[1.5px] border-primary-500 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="Cari nama atau NIK ibu...">

                        <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-40 overflow-y-auto">
                            @forelse($daftarIbu as $ibu)
                                <label @click="open = false" class="flex items-center gap-3 px-3 py-2.5 hover:bg-primary-50 cursor-pointer transition-colors {{ $ibu_id == $ibu->id ? 'bg-primary-50' : '' }}">
                                    <input type="radio" wire:model="ibu_id" value="{{ $ibu->id }}" class="hidden">
                                    <div>
                                        <span class="text-sm font-medium text-slate-800">{{ $ibu->nama }}</span>
                                        <span class="text-xs text-slate-400 ml-2 font-mono">{{ $ibu->nik }}</span>
                                    </div>
                                </label>
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
