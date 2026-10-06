<?php

use App\Models\Ibu;
use App\Models\Kehamilan;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public ?int $kehamilanId = null;
    public string $ibu_id = '';
    public int $kehamilan_ke = 1;
    public string $hpht = '';
    public string $hpl = '';
    public string $bb_sebelum_hamil = '';
    public string $tinggi_badan = '';
    public string $lila_awal = '';
    public string $status_kehamilan = 'aktif';
    public string $catatan_risiko = '';
    public string $searchIbu = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $k = Kehamilan::findOrFail($id);
            $this->kehamilanId = $k->id;
            $this->ibu_id = (string) $k->ibu_id;
            $this->kehamilan_ke = $k->kehamilan_ke;
            $this->hpht = $k->hpht ? $k->hpht->format('Y-m-d') : '';
            $this->hpl = $k->hpl ? $k->hpl->format('Y-m-d') : '';
            $this->bb_sebelum_hamil = (string) $k->bb_sebelum_hamil;
            $this->tinggi_badan = (string) $k->tinggi_badan;
            $this->lila_awal = $k->lila_awal ? (string) $k->lila_awal : '';
            $this->status_kehamilan = $k->status_kehamilan;
            $this->catatan_risiko = $k->catatan_risiko ?? '';
        } else {
            // Default tanggal HPHT kira-kira 4 minggu lalu
            $this->hpht = now()->subWeeks(4)->format('Y-m-d');
            $this->hitungHplOtomatis();
        }
    }

    public function updatedHpht(): void
    {
        $this->hitungHplOtomatis();
    }

    public function hitungHplOtomatis(): void
    {
        if ($this->hpht) {
            $this->hpl = Kehamilan::hitungHpl($this->hpht);
        }
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

    public function save(): void
    {
        $rules = [
            'ibu_id'            => 'required|exists:ibu,id',
            'kehamilan_ke'      => 'required|integer|min:1|max:20',
            'hpht'              => 'required|date',
            'hpl'               => 'required|date',
            'bb_sebelum_hamil'  => 'required|numeric|min:25|max:200',
            'tinggi_badan'      => 'required|numeric|min:100|max:220',
            'lila_awal'         => 'nullable|numeric|min:10|max:50',
            'status_kehamilan'  => 'required|in:aktif,melahirkan,keguguran',
            'catatan_risiko'    => 'nullable|string',
        ];

        $validated = $this->validate($rules, [
            'ibu_id.required'           => 'Ibu wajib dipilih.',
            'kehamilan_ke.required'     => 'Kehamilan ke- (Gravida) wajib diisi.',
            'hpht.required'             => 'HPHT wajib diisi.',
            'hpl.required'              => 'Taksiran persalinan (HPL) wajib diisi.',
            'bb_sebelum_hamil.required' => 'Berat badan sebelum hamil wajib diisi.',
            'tinggi_badan.required'     => 'Tinggi badan wajib diisi.',
        ]);

        // Hitung IMT dan Kategori
        $bb = (float) $this->bb_sebelum_hamil;
        $tb = (float) $this->tinggi_badan;
        $imt = Kehamilan::hitungImt($bb, $tb);
        $kategoriImt = Kehamilan::tentukanKategoriImt($imt);
        $statusKek = $this->lila_awal ? ((float) $this->lila_awal < 23.5) : false;

        $data = array_merge($validated, [
            'imt_pra_hamil' => $imt,
            'kategori_imt'  => $kategoriImt,
            'lila_awal'     => $this->lila_awal ?: null,
            'status_kek'    => $statusKek,
        ]);

        if ($this->kehamilanId) {
            Kehamilan::findOrFail($this->kehamilanId)->update($data);
            session()->flash('success', 'Data profil kehamilan berhasil diperbarui.');
        } else {
            Kehamilan::create($data);
            session()->flash('success', 'Data kehamilan baru berhasil didaftarkan.');
        }

        $this->redirect(route('kesehatan-ibu.index'));
    }

    public function with(): array
    {
        $daftarIbu = Ibu::when($this->searchIbu, fn ($q) =>
            $q->where('nama', 'like', "%{$this->searchIbu}%")->orWhere('nik', 'like', "%{$this->searchIbu}%")
        )->orderBy('nama')->limit(20)->get();

        $selectedIbu = $this->ibu_id ? Ibu::find($this->ibu_id) : null;

        // Preview IMT
        $previewImt = null;
        $previewKategori = null;
        if (is_numeric($this->bb_sebelum_hamil) && is_numeric($this->tinggi_badan) && (float)$this->bb_sebelum_hamil > 0 && (float)$this->tinggi_badan > 0) {
            $previewImt = Kehamilan::hitungImt((float)$this->bb_sebelum_hamil, (float)$this->tinggi_badan);
            $previewKategori = Kehamilan::tentukanKategoriImt($previewImt);
        }

        return compact('daftarIbu', 'selectedIbu', 'previewImt', 'previewKategori');
    }

    public function title(): string
    {
        return $this->kehamilanId ? 'Edit Profil Kehamilan' : 'Registrasi Ibu Hamil Baru';
    }
};
?>

<div>
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('kesehatan-ibu.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h2 class="font-heading font-bold text-xl text-slate-900">{{ $kehamilanId ? 'Edit Profil Kehamilan' : 'Registrasi Ibu Hamil Baru' }}</h2>
                <p class="text-xs text-slate-500">Isi data kehamilan awal untuk pemantauan Buku KIA & kurva kenaikan berat badan</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-6">
                {{-- PILIH IBU --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Ibu Hamil <span class="text-red-500">*</span></label>
                    
                    <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" 
                         class="w-full h-11 px-3.5 border-[1.5px] {{ $errors->has('ibu_id') ? 'border-red-400' : 'border-slate-300 hover:border-slate-400' }} rounded-xl text-sm flex items-center justify-between bg-white cursor-pointer transition-all">
                        @if($selectedIbu)
                            <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                <span class="font-bold text-slate-800 truncate">{{ $selectedIbu->nama }}</span>
                                <span class="text-xs text-slate-400 font-mono">NIK: {{ $selectedIbu->nik }}</span>
                                <span class="text-xs text-slate-400">· Tgl Lahir: {{ $selectedIbu->tanggal_lahir->format('d/m/Y') }}</span>
                            </div>
                            <button type="button" wire:click.stop="resetIbu" class="p-1 text-slate-400 hover:text-red-500">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        @else
                            <span class="text-slate-400">Cari nama atau NIK ibu...</span>
                            <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        @endif
                    </div>

                    <div x-show="open" style="display: none;" class="relative">
                        <input x-ref="searchInput" wire:model.live.debounce.300ms="searchIbu" type="text"
                               placeholder="Ketik nama atau NIK ibu..."
                               class="w-full h-11 px-3.5 border-2 border-pink-500 rounded-xl text-sm outline-none">
                        <div class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-52 overflow-y-auto divide-y divide-slate-100">
                            @forelse($daftarIbu as $ibu)
                                <button type="button" wire:click="selectIbu({{ $ibu->id }})" @click="open = false"
                                        class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-pink-50 text-left transition-colors {{ $ibu_id == $ibu->id ? 'bg-pink-50 text-pink-700' : '' }}">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $ibu->nama }}</p>
                                        <p class="text-xs text-slate-400">NIK: {{ $ibu->nik }} · Alamat: {{ $ibu->alamat }}</p>
                                    </div>
                                    <span class="text-xs font-bold text-pink-600 bg-pink-100/70 px-2 py-0.5 rounded">Pilih</span>
                                </button>
                            @empty
                                <p class="p-4 text-sm text-center text-slate-400">Data ibu tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                    @error('ibu_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Gravida & Tanggal --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kehamilan Ke- (Gravida) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="kehamilan_ke" min="1" max="20"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        @error('kehamilan_ke') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">HPHT (Hari Pertama Haid Terakhir) <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="hpht"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        @error('hpht') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Taksiran Persalinan (HPL) <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="hpl"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        <span class="text-[10px] text-slate-400">Dihitung otomatis (Rumus Naegele)</span>
                        @error('hpl') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Fisik Pra-Hamil (BB, TB, LiLA) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">BB Sebelum Hamil (kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" wire:model.live.debounce.300ms="bb_sebelum_hamil" placeholder="cth: 50.5"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        @error('bb_sebelum_hamil') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" wire:model.live.debounce.300ms="tinggi_badan" placeholder="cth: 155.0"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        @error('tinggi_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">LiLA Awal (cm) <span class="text-slate-400 font-normal">opsional</span></label>
                        <input type="number" step="0.1" wire:model="lila_awal" placeholder="cth: 24.5"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-xl text-sm focus:border-pink-500 outline-none">
                        <span class="text-[10px] text-slate-400">&lt; 23.5 cm: Risiko KEK</span>
                    </div>
                </div>

                {{-- Live Preview IMT & Rekomendasi Kenaikan BB Buku KIA --}}
                @if($previewImt)
                    <div class="p-4 rounded-xl bg-pink-50/60 border border-pink-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-xs font-semibold text-pink-700">Hasil Indeks Massa Tubuh (IMT) Pra-Hamil:</span>
                                <p class="text-base font-bold text-slate-900">
                                    {{ $previewImt }} kg/m² 
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold ml-1
                                        {{ $previewKategori === 'normal' ? 'bg-green-100 text-green-700' : ($previewKategori === 'kurang' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        Kategori: {{ strtoupper($previewKategori) }}
                                    </span>
                                </p>
                            </div>
                            <div class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-pink-100">
                                <span class="font-bold text-pink-800">Target Kenaikan BB Kemenkes:</span>
                                @if($previewKategori === 'kurus')
                                    <p>12.5 s/d 18.0 kg (kurus)</p>
                                @elseif($previewKategori === 'normal')
                                    <p>11.5 s/d 16.0 kg (normal)</p>
                                @elseif($previewKategori === 'lebih')
                                    <p>7.0 s/d 11.5 kg (kelebihan BB)</p>
                                @else
                                    <p>5.0 s/d 9.0 kg (obesitas)</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Status & Catatan Risiko --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kehamilan Saat Ini</label>
                        <select wire:model="status_kehamilan" class="w-full h-11 px-3.5 border rounded-xl text-sm bg-white border-slate-300">
                            <option value="aktif">Aktif (Sedang Hamil)</option>
                            <option value="melahirkan">Sudah Melahirkan</option>
                            <option value="keguguran">Keguguran</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Faktor Risiko / Catatan Medis <span class="text-slate-400 font-normal">opsional</span></label>
                        <input type="text" wire:model="catatan_risiko" placeholder="cth: Riwayat hipertensi, anemia, jarak anak dekat"
                               class="w-full h-11 px-3.5 border rounded-xl text-sm border-slate-300 focus:border-pink-500 outline-none">
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 h-11 px-6 bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm rounded-xl transition-all shadow-md active:scale-[0.98]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan Profil Kehamilan
                    </button>
                    <a href="{{ route('kesehatan-ibu.index') }}" class="inline-flex items-center h-11 px-5 border border-slate-300 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
