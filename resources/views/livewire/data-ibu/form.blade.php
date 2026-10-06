<?php

use App\Models\Ibu;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $ibuId = null;
    public string $nik = '';
    public string $nama = '';
    public string $tanggal_lahir = '';
    public string $alamat = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $ibu = Ibu::findOrFail($id);
            $this->ibuId = $ibu->id;
            $this->nik = $ibu->nik;
            $this->nama = $ibu->nama;
            $this->tanggal_lahir = $ibu->tanggal_lahir->format('Y-m-d');
            $this->alamat = $ibu->alamat;
        }
    }

    public function save(): void
    {
        $rules = [
            'nik' => 'required|digits:16|unique:ibu,nik' . ($this->ibuId ? ",{$this->ibuId}" : ''),
            'nama' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
        ];

        $validated = $this->validate($rules, [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);

        if ($this->ibuId) {
            Ibu::findOrFail($this->ibuId)->update($validated);
            session()->flash('success', 'Data ibu berhasil diperbarui.');
        } else {
            Ibu::create($validated);
            session()->flash('success', 'Data ibu berhasil ditambahkan.');
        }

        $this->redirect(route('data-ibu.index'));
    }

    public function title(): string
    {
        return $this->ibuId ? 'Edit Data Ibu' : 'Tambah Data Ibu';
    }
};
?>
<div>
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('data-ibu.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h2 class="font-heading font-bold text-xl text-slate-900">{{ $ibuId ? 'Edit Data Ibu' : 'Tambah Data Ibu' }}</h2>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-5">
                {{-- NIK --}}
                <div>
                    <label for="nik" class="block text-sm font-medium text-slate-700 mb-1.5">NIK <span class="text-red-500">*</span></label>
                    <input wire:model="nik" type="text" id="nik" maxlength="16" inputmode="numeric"
                           class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm font-mono transition-all outline-none {{ $errors->has('nik') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500/20' }} focus:ring-[3px]"
                           placeholder="Masukkan NIK 16 digit">
                    @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Ibu <span class="text-red-500">*</span></label>
                    <input wire:model="nama" type="text" id="nama"
                           class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none {{ $errors->has('nama') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500/20' }} focus:ring-[3px]"
                           placeholder="Masukkan nama lengkap">
                    @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input wire:model="tanggal_lahir" type="date" id="tanggal_lahir"
                           class="w-full h-11 px-3.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none {{ $errors->has('tanggal_lahir') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500/20' }} focus:ring-[3px]">
                    @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1.5">Alamat <span class="text-red-500">*</span></label>
                    <textarea wire:model="alamat" id="alamat" rows="3"
                              class="w-full px-3.5 py-2.5 border-[1.5px] rounded-[10px] text-sm transition-all outline-none resize-none {{ $errors->has('alamat') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500/20' }} focus:ring-[3px]"
                              placeholder="Masukkan alamat lengkap"></textarea>
                    @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97] w-full sm:w-auto">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan
                    </button>
                    <a href="{{ route('data-ibu.index') }}" class="inline-flex items-center justify-center h-10 px-6 border border-slate-300 text-slate-600 font-semibold text-sm rounded-[10px] hover:bg-slate-50 transition-all w-full sm:w-auto">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
