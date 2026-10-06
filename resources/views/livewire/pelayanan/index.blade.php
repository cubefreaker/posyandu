<?php

use App\Models\Anak;
use App\Models\Ibu;
use App\Models\Imunisasi;
use App\Models\JenisImunisasi;
use App\Models\Penimbangan;
use App\Models\Vitamin;
use App\Services\StatusGiziService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Pelayanan Posyandu Terpadu')] class extends Component
{
    // Filter & Tanggal Pelayanan
    public string $tanggal_pelayanan = '';

    // Pemilihan Anak
    public ?int $selectedAnakId = null;
    public string $searchAnak = '';

    // Form Tambah Warga Baru Cepat (Ibu + Anak)
    public bool $isFormBaru = false;
    public string $nik_ibu = '';
    public string $nama_ibu = '';
    public string $tanggal_lahir_ibu = '';
    public string $alamat_ibu = '';
    public string $telepon_ibu = '';
    public string $nama_anak = '';
    public string $tanggal_lahir_anak = '';
    public string $jenis_kelamin_anak = 'L';

    // Pengukuran & Penimbangan
    public string $berat_badan = '';
    public string $tinggi_badan = '';
    public string $lingkar_kepala = '';
    public string $lila = '';

    // Imunisasi
    public bool $berikan_imunisasi = false;
    public string $jenis_imunisasi_id = '';
    public string $keterangan_imunisasi = '';

    // Vitamin
    public bool $berikan_vitamin = false;
    public string $jenis_vitamin = '';
    public string $keterangan_vitamin = '';

    // Mode Edit jika kader salah input hari ini
    public ?int $editingPenimbanganId = null;

    public function mount(): void
    {
        $this->tanggal_pelayanan = now()->format('Y-m-d');
    }

    public function selectAnak(int $id): void
    {
        $this->selectedAnakId = $id;
        $this->searchAnak = '';
        $this->resetPelayananForm();
        $this->autoDetectVitamin();
    }

    public function resetPilihanAnak(): void
    {
        $this->selectedAnakId = null;
        $this->searchAnak = '';
        $this->resetPelayananForm();
    }

    public function toggleFormBaru(): void
    {
        $this->isFormBaru = !$this->isFormBaru;
        if ($this->isFormBaru) {
            $this->selectedAnakId = null;
            $this->resetPelayananForm();
        }
    }

    public function simpanWargaBaru(): void
    {
        $this->validate([
            'nik_ibu'            => 'required|digits:16|unique:ibu,nik',
            'nama_ibu'           => 'required|string|max:100',
            'tanggal_lahir_ibu'  => 'required|date',
            'alamat_ibu'         => 'required|string',
            'telepon_ibu'        => 'nullable|string|max:20',
            'nama_anak'          => 'required|string|max:100',
            'tanggal_lahir_anak' => 'required|date',
            'jenis_kelamin_anak' => 'required|in:L,P',
        ], [
            'nik_ibu.required'            => 'NIK ibu wajib diisi.',
            'nik_ibu.digits'              => 'NIK ibu harus 16 digit.',
            'nik_ibu.unique'              => 'NIK ibu sudah terdaftar.',
            'nama_ibu.required'           => 'Nama ibu wajib diisi.',
            'nama_anak.required'          => 'Nama anak wajib diisi.',
            'tanggal_lahir_anak.required' => 'Tanggal lahir anak wajib diisi.',
        ]);

        DB::transaction(function () {
            $ibu = Ibu::create([
                'nik'           => $this->nik_ibu,
                'nama'          => $this->nama_ibu,
                'tanggal_lahir' => $this->tanggal_lahir_ibu,
                'alamat'        => $this->alamat_ibu,
                'telepon'       => $this->telepon_ibu,
            ]);

            $anak = Anak::create([
                'ibu_id'        => $ibu->id,
                'nama'          => $this->nama_anak,
                'tanggal_lahir' => $this->tanggal_lahir_anak,
                'jenis_kelamin' => $this->jenis_kelamin_anak,
            ]);

            $this->selectedAnakId = $anak->id;
        });

        $this->isFormBaru = false;
        $this->resetWargaBaruForm();
        $this->autoDetectVitamin();
        session()->flash('success', 'Data Ibu dan Anak baru berhasil didaftarkan! Silakan lanjutkan penimbangan.');
    }

    public function autoDetectVitamin(): void
    {
        if (!$this->selectedAnakId) return;

        $anak = Anak::find($this->selectedAnakId);
        if ($anak) {
            $usiaBulan = $anak->usia_in_bulan;
            if ($usiaBulan >= 6 && $usiaBulan <= 11) {
                $this->jenis_vitamin = 'kapsul_biru';
            } elseif ($usiaBulan >= 12 && $usiaBulan <= 59) {
                $this->jenis_vitamin = 'kapsul_merah';
            } else {
                $this->jenis_vitamin = '';
            }
        }
    }

    public function simpanPelayanan(): void
    {
        if (!$this->selectedAnakId) {
            session()->flash('error', 'Silakan pilih balita terlebih dahulu.');
            return;
        }

        $rules = [
            'tanggal_pelayanan' => 'required|date',
            'berat_badan'       => 'required|numeric|min:0.5|max:99',
            'tinggi_badan'      => 'required|numeric|min:20|max:200',
            'lingkar_kepala'    => 'nullable|numeric|min:10|max:100',
            'lila'              => 'nullable|numeric|min:5|max:50',
        ];

        if ($this->berikan_imunisasi) {
            $rules['jenis_imunisasi_id'] = 'required|exists:jenis_imunisasi,id';
        }

        if ($this->berikan_vitamin) {
            $rules['jenis_vitamin'] = 'required|in:kapsul_biru,kapsul_merah';
        }

        $validated = $this->validate($rules, [
            'berat_badan.required'  => 'Berat badan wajib diisi.',
            'tinggi_badan.required' => 'Tinggi badan wajib diisi.',
            'jenis_imunisasi_id.required' => 'Pilih jenis vaksin imunisasi.',
            'jenis_vitamin.required'      => 'Pilih jenis kapsul vitamin A.',
        ]);

        $anak = Anak::findOrFail($this->selectedAnakId);
        $tglPelayanan = Carbon::parse($this->tanggal_pelayanan);
        $usiaInBulan = $anak->tanggal_lahir->diffInDays($tglPelayanan) / 30.4375;

        // Hitung status gizi otomatis
        $statusGizi = StatusGiziService::hitung(
            round($usiaInBulan),
            $anak->jenis_kelamin,
            (float) $this->berat_badan,
            (float) $this->tinggi_badan
        );

        DB::transaction(function () use ($anak, $statusGizi) {
            if ($this->editingPenimbanganId) {
                Penimbangan::where('id', $this->editingPenimbanganId)->update([
                    'tanggal_pelayanan' => $this->tanggal_pelayanan,
                    'berat_badan'       => $this->berat_badan,
                    'tinggi_badan'      => $this->tinggi_badan,
                    'lingkar_kepala'    => $this->lingkar_kepala ?: null,
                    'lila'              => $this->lila ?: null,
                    'zscore_bbu'        => $statusGizi['zscore_bbu'],
                    'zscore_tbu'        => $statusGizi['zscore_tbu'],
                    'zscore_bbtb'       => $statusGizi['zscore_bbtb'],
                    'status_bbu'        => $statusGizi['status_bbu'],
                    'status_tbu'        => $statusGizi['status_tbu'],
                    'status_bbtb'       => $statusGizi['status_bbtb'],
                ]);
            } else {
                Penimbangan::create([
                    'anak_id'           => $anak->id,
                    'tanggal_pelayanan' => $this->tanggal_pelayanan,
                    'berat_badan'       => $this->berat_badan,
                    'tinggi_badan'      => $this->tinggi_badan,
                    'lingkar_kepala'    => $this->lingkar_kepala ?: null,
                    'lila'              => $this->lila ?: null,
                    'zscore_bbu'        => $statusGizi['zscore_bbu'],
                    'zscore_tbu'        => $statusGizi['zscore_tbu'],
                    'zscore_bbtb'       => $statusGizi['zscore_bbtb'],
                    'status_bbu'        => $statusGizi['status_bbu'],
                    'status_tbu'        => $statusGizi['status_tbu'],
                    'status_bbtb'       => $statusGizi['status_bbtb'],
                ]);
            }

            // Simpan Imunisasi jika dipilih
            if ($this->berikan_imunisasi && $this->jenis_imunisasi_id) {
                Imunisasi::updateOrCreate(
                    [
                        'anak_id' => $anak->id,
                        'jenis_imunisasi_id' => $this->jenis_imunisasi_id,
                    ],
                    [
                        'tanggal_imunisasi' => $this->tanggal_pelayanan,
                        'keterangan'        => $this->keterangan_imunisasi ?: 'Diberikan saat pelayanan posyandu',
                    ]
                );
            }

            // Simpan Vitamin jika dipilih
            if ($this->berikan_vitamin && $this->jenis_vitamin) {
                Vitamin::create([
                    'anak_id'           => $anak->id,
                    'tanggal_pemberian' => $this->tanggal_pelayanan,
                    'jenis_vitamin'     => $this->jenis_vitamin,
                    'keterangan'        => $this->keterangan_vitamin ?: 'Diberikan saat pelayanan posyandu',
                ]);
            }
        });

        session()->flash('success', "Pelayanan untuk ananda {$anak->nama} berhasil dicatat!");
        $this->resetPelayananForm();
        $this->selectedAnakId = null;
        $this->editingPenimbanganId = null;
    }

    public function editPelayanan(int $penimbanganId): void
    {
        $p = Penimbangan::with('anak')->findOrFail($penimbanganId);
        $this->editingPenimbanganId = $p->id;
        $this->selectedAnakId = $p->anak_id;
        $this->tanggal_pelayanan = $p->tanggal_pelayanan->format('Y-m-d');
        $this->berat_badan = (string) $p->berat_badan;
        $this->tinggi_badan = (string) $p->tinggi_badan;
        $this->lingkar_kepala = $p->lingkar_kepala ? (string) $p->lingkar_kepala : '';
        $this->lila = $p->lila ? (string) $p->lila : '';

        // Cari apakah ada imunisasi hari ini
        $im = Imunisasi::where('anak_id', $p->anak_id)->whereDate('tanggal_imunisasi', $p->tanggal_pelayanan)->first();
        if ($im) {
            $this->berikan_imunisasi = true;
            $this->jenis_imunisasi_id = (string) $im->jenis_imunisasi_id;
            $this->keterangan_imunisasi = $im->keterangan ?? '';
        } else {
            $this->berikan_imunisasi = false;
        }

        // Cari apakah ada vitamin hari ini
        $vit = Vitamin::where('anak_id', $p->anak_id)->whereDate('tanggal_pemberian', $p->tanggal_pelayanan)->first();
        if ($vit) {
            $this->berikan_vitamin = true;
            $this->jenis_vitamin = $vit->jenis_vitamin;
            $this->keterangan_vitamin = $vit->keterangan ?? '';
        } else {
            $this->berikan_vitamin = false;
        }

        $this->dispatch('scroll-to-top');
    }

    public function batalEdit(): void
    {
        $this->editingPenimbanganId = null;
        $this->selectedAnakId = null;
        $this->resetPelayananForm();
    }

    private function resetPelayananForm(): void
    {
        $this->berat_badan = '';
        $this->tinggi_badan = '';
        $this->lingkar_kepala = '';
        $this->lila = '';
        $this->berikan_imunisasi = false;
        $this->jenis_imunisasi_id = '';
        $this->keterangan_imunisasi = '';
        $this->berikan_vitamin = false;
        $this->jenis_vitamin = '';
        $this->keterangan_vitamin = '';
    }

    private function resetWargaBaruForm(): void
    {
        $this->nik_ibu = '';
        $this->nama_ibu = '';
        $this->tanggal_lahir_ibu = '';
        $this->alamat_ibu = '';
        $this->telepon_ibu = '';
        $this->nama_anak = '';
        $this->tanggal_lahir_anak = '';
        $this->jenis_kelamin_anak = 'L';
    }

    public function with(): array
    {
        $daftarAnak = Anak::with('ibu')
            ->when($this->searchAnak, fn ($q) =>
                $q->where('nama', 'like', "%{$this->searchAnak}%")
                  ->orWhereHas('ibu', fn ($q2) => $q2->where('nama', 'like', "%{$this->searchAnak}%")->orWhere('nik', 'like', "%{$this->searchAnak}%"))
            )
            ->orderBy('nama')
            ->limit(15)
            ->get();

        $selectedAnak = $this->selectedAnakId ? Anak::with(['ibu', 'penimbangan', 'imunisasi.jenisImunisasi'])->find($this->selectedAnakId) : null;
        $semuaJenisImunisasi = JenisImunisasi::orderBy('urutan')->get();

        // Hitung preview status gizi jika BB dan TB sudah diisi
        $previewGizi = null;
        if ($selectedAnak && is_numeric($this->berat_badan) && is_numeric($this->tinggi_badan) && (float)$this->berat_badan > 0 && (float)$this->tinggi_badan > 0) {
            $tgl = Carbon::parse($this->tanggal_pelayanan ?: now());
            $usiaBulan = round($selectedAnak->tanggal_lahir->diffInDays($tgl) / 30.4375);
            $previewGizi = StatusGiziService::hitung(
                (int) $usiaBulan,
                $selectedAnak->jenis_kelamin,
                (float) $this->berat_badan,
                (float) $this->tinggi_badan
            );
        }

        // Riwayat & Rekap Pelayanan Hari Ini (Tanggal Terpilih)
        $tglFilter = $this->tanggal_pelayanan ?: now()->format('Y-m-d');
        $pelayananHariIni = Penimbangan::with(['anak.ibu'])
            ->whereDate('tanggal_pelayanan', $tglFilter)
            ->latest()
            ->get();

        $totalAnakHariIni = $pelayananHariIni->unique('anak_id')->count();
        $totalImunisasiHariIni = Imunisasi::whereDate('tanggal_imunisasi', $tglFilter)->count();
        $totalVitaminHariIni = Vitamin::whereDate('tanggal_pemberian', $tglFilter)->count();

        // Hitung distribusi status gizi hari ini
        $giziHariIni = [
            'baik'   => $pelayananHariIni->where('status_bbu', 'baik')->count(),
            'kurang' => $pelayananHariIni->where('status_bbu', 'kurang')->count(),
            'buruk'  => $pelayananHariIni->where('status_bbu', 'buruk')->count(),
            'lebih'  => $pelayananHariIni->where('status_bbu', 'lebih')->count(),
        ];

        return compact(
            'daftarAnak',
            'selectedAnak',
            'semuaJenisImunisasi',
            'previewGizi',
            'pelayananHariIni',
            'totalAnakHariIni',
            'totalImunisasiHariIni',
            'totalVitaminHariIni',
            'giziHariIni'
        );
    }
};
?>

<div>
    {{-- Top Alert / Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-primary-100 text-primary-600 rounded-xl">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </span>
                <div>
                    <h2 class="font-heading font-bold text-xl text-slate-900">Pelayanan Posyandu Terpadu</h2>
                    <p class="text-sm text-slate-500">Form satu pintu: Pendaftaran, Penimbangan Balita, Imunisasi, Vitamin, dan Rekapitulasi Harian</p>
                </div>
            </div>
        </div>

        {{-- Tanggal Pelayanan Picker & Cetak Laporan --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-3 py-2 border border-slate-200 rounded-xl shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal:</span>
                <input type="date" wire:model.live="tanggal_pelayanan" class="text-sm font-semibold text-slate-800 bg-transparent outline-none">
            </div>

            <a href="{{ route('laporan.export-pdf', ['start_date' => $tanggal_pelayanan, 'end_date' => $tanggal_pelayanan, 'tipe' => 'harian']) }}"
               target="_blank"
               class="inline-flex items-center gap-2 h-10 px-4 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Cetak Laporan Hari Ini
            </a>
        </div>
    </div>

    {{-- Mode Edit Banner --}}
    @if($editingPenimbanganId)
        <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Anda sedang mengedit data pelayanan balita terpilih. Koreksi angka lalu klik "Perbarui Pelayanan".</span>
            </div>
            <button wire:click="batalEdit" class="text-xs font-semibold bg-white border border-amber-300 text-amber-700 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
                Batal Edit
            </button>
        </div>
    @endif

    {{-- Main Container: 2 Kolom --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        {{-- KOLOM KIRI: FORM SATU PINTU (7 Kolom) --}}
        <div class="lg:span-7 lg:col-span-7 space-y-6">
            
            {{-- SEKSI 1: PILIH / DAFTAR BALITA & IBU --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-xs">1</span>
                        <h3 class="font-heading font-bold text-slate-800 text-base">Identitas Balita & Ibu</h3>
                    </div>
                    
                    <button type="button" wire:click="toggleFormBaru" class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all {{ $isFormBaru ? 'bg-slate-100 text-slate-600' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }}">
                        {{ $isFormBaru ? '← Cari Balita Terdaftar' : '+ Balita / Ibu Baru' }}
                    </button>
                </div>

                @if(!$isFormBaru)
                    {{-- Pencarian Cepat Balita --}}
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Pilih Balita Sasaran</label>
                        
                        <div x-show="!open" @click="open = true; $nextTick(() => $refs.searchInput.focus())" 
                             class="w-full min-h-[46px] px-3.5 border-[1.5px] {{ $errors->has('selectedAnakId') ? 'border-red-400' : 'border-slate-300 hover:border-slate-400' }} rounded-xl text-sm flex items-center justify-between bg-white cursor-pointer transition-all">
                            @if($selectedAnak)
                                <div class="flex items-center gap-2 overflow-hidden min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $selectedAnak->jenis_kelamin === 'L' ? 'bg-blue-500' : 'bg-pink-500' }}"></span>
                                    <span class="font-bold text-slate-900 truncate">{{ $selectedAnak->nama }}</span>
                                    <span class="text-xs text-slate-400">· Usia: {{ $selectedAnak->usia }}</span>
                                    <span class="text-xs text-slate-400 truncate">· Ibu: {{ $selectedAnak->ibu->nama ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 ml-2">
                                    <button type="button" wire:click.stop="resetPilihanAnak" class="p-1 text-slate-400 hover:text-red-500 rounded-md">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            @else
                                <span class="text-slate-400">Ketik nama anak, nama ibu, atau NIK ibu...</span>
                                <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            @endif
                        </div>

                        {{-- Dropdown Search --}}
                        <div x-show="open" style="display: none;" class="relative">
                            <input x-ref="searchInput" 
                                   wire:model.live.debounce.250ms="searchAnak" 
                                   type="text"
                                   placeholder="Ketik nama balita atau NIK ibu..."
                                   class="w-full h-11 px-3.5 border-2 border-primary-500 rounded-xl text-sm focus:outline-none">
                            <div class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                                @forelse($daftarAnak as $anak)
                                    <button type="button" 
                                            wire:key="opt-anak-{{ $anak->id }}"
                                            wire:click="selectAnak({{ $anak->id }})" 
                                            @click="open = false" 
                                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-primary-50 text-left transition-colors {{ $selectedAnakId == $anak->id ? 'bg-primary-50/80 font-semibold' : '' }}">
                                        <div>
                                            <p class="text-sm font-medium text-slate-800">{{ $anak->nama }} ({{ $anak->jenis_kelamin }})</p>
                                            <p class="text-xs text-slate-400">Ibu: {{ $anak->ibu->nama ?? '-' }} · Usia: {{ $anak->usia }} · NIK: {{ $anak->ibu->nik ?? '-' }}</p>
                                        </div>
                                        <span class="text-xs font-semibold text-primary-600 bg-primary-100/60 px-2 py-0.5 rounded">Pilih</span>
                                    </button>
                                @empty
                                    <div class="p-4 text-center text-sm text-slate-400">
                                        Tidak menemukan balita. Klik "+ Balita / Ibu Baru" di atas jika belum terdaftar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Form Cepat Tambah Ibu & Balita Baru --}}
                    <div class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-xs font-bold text-primary-700 uppercase tracking-wider">Pendaftaran Warga Baru Sekaligus</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">NIK Ibu <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nik_ibu" maxlength="16" placeholder="16 digit NIK" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                @error('nik_ibu') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Ibu <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="nama_ibu" placeholder="Nama lengkap ibu" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                @error('nama_ibu') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Lahir Ibu <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="tanggal_lahir_ibu" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                @error('tanggal_lahir_ibu') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">No. WhatsApp / HP</label>
                                <input type="text" wire:model="telepon_ibu" placeholder="08xxxxxxxx" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Alamat Domisili <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="alamat_ibu" placeholder="Dusun / RT RW" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                @error('alamat_ibu') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-xs font-bold text-secondary-600 uppercase tracking-wider mb-2">Identitas Balita</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Anak <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="nama_anak" placeholder="Nama anak" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                    @error('nama_anak') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="tanggal_lahir_anak" class="w-full h-9 px-3 border rounded-lg text-sm bg-white">
                                    @error('tanggal_lahir_anak') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_kelamin_anak" class="w-full h-9 px-2 border rounded-lg text-sm bg-white">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="toggleFormBaru" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-200 rounded-lg">Batal</button>
                            <button type="button" wire:click="simpanWargaBaru" class="px-4 py-1.5 text-xs font-bold bg-primary-600 text-white rounded-lg hover:bg-primary-700">Daftarkan & Mulai Pelayanan</button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- SEKSI 2: PENGUKURAN & PENIMBANGAN FISIK --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">2</span>
                    <h3 class="font-heading font-bold text-slate-800 text-base">Penimbangan & Pengukuran Fisik</h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {{-- BB --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.05" wire:model.live.debounce.300ms="berat_badan" placeholder="cth: 9.5"
                               class="w-full h-11 px-3 border-[1.5px] rounded-xl text-base font-bold text-slate-900 {{ $errors->has('berat_badan') ? 'border-red-400' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' }} focus:ring-[3px] outline-none">
                        @error('berat_badan') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- TB --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tinggi / PJ (cm) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" wire:model.live.debounce.300ms="tinggi_badan" placeholder="cth: 75.0"
                               class="w-full h-11 px-3 border-[1.5px] rounded-xl text-base font-bold text-slate-900 {{ $errors->has('tinggi_badan') ? 'border-red-400' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' }} focus:ring-[3px] outline-none">
                        @error('tinggi_badan') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- LK --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Lingkar Kepala <span class="text-slate-400 text-[10px]">(opsional)</span></label>
                        <input type="number" step="0.1" wire:model="lingkar_kepala" placeholder="cm"
                               class="w-full h-11 px-3 border rounded-xl text-sm border-slate-300 focus:border-emerald-500 outline-none">
                    </div>

                    {{-- LiLA --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">LiLA <span class="text-slate-400 text-[10px]">(opsional)</span></label>
                        <input type="number" step="0.1" wire:model="lila" placeholder="cm"
                               class="w-full h-11 px-3 border rounded-xl text-sm border-slate-300 focus:border-emerald-500 outline-none">
                    </div>
                </div>

                {{-- Live Preview Status Gizi --}}
                @if($previewGizi)
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hasil Hitung Gizi Kemenkes (Real-Time):</span>
                            <span class="text-xs text-slate-400 font-mono">Z-Score: {{ $previewGizi['zscore_bbu'] }} SD</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="p-2.5 rounded-lg bg-white border border-slate-100 text-center">
                                <span class="block text-[10px] text-slate-400 font-medium">BB/U (Berat/Usia)</span>
                                <span class="text-xs font-bold capitalize 
                                    {{ $previewGizi['status_bbu'] === 'baik' ? 'text-green-600' : ($previewGizi['status_bbu'] === 'kurang' ? 'text-amber-600' : 'text-red-600') }}">
                                    Gizi {{ $previewGizi['status_bbu'] }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-white border border-slate-100 text-center">
                                <span class="block text-[10px] text-slate-400 font-medium">TB/U (Tinggi/Usia)</span>
                                <span class="text-xs font-bold capitalize 
                                    {{ $previewGizi['status_tbu'] === 'normal' ? 'text-green-600' : ($previewGizi['status_tbu'] === 'pendek' ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ str_replace('_', ' ', $previewGizi['status_tbu']) }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-white border border-slate-100 text-center">
                                <span class="block text-[10px] text-slate-400 font-medium">BB/TB (Gizi Akut)</span>
                                <span class="text-xs font-bold capitalize 
                                    {{ $previewGizi['status_bbtb'] === 'gizi_baik' ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ str_replace('_', ' ', $previewGizi['status_bbtb']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- SEKSI 3: PELAYANAN TAMBAHAN (IMUNISASI & VITAMIN A) --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">3</span>
                    <h3 class="font-heading font-bold text-slate-800 text-base">Pelayanan Tambahan (Imunisasi & Vitamin A)</h3>
                </div>

                <div class="space-y-4">
                    {{-- Imunisasi Checkbox & Sub-form --}}
                    <div class="p-3.5 rounded-xl border {{ $berikan_imunisasi ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200' }} transition-colors">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model.live="berikan_imunisasi" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm font-bold text-slate-800">Pemberian Vaksin Imunisasi Hari Ini</span>
                        </label>

                        @if($berikan_imunisasi)
                            <div class="mt-3 pt-3 border-t border-blue-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Vaksin <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_imunisasi_id" class="w-full h-10 px-3 border rounded-xl text-sm bg-white">
                                        <option value="">— Pilih Vaksin —</option>
                                        @foreach($semuaJenisImunisasi as $jenis)
                                            <option value="{{ $jenis->id }}">{{ $jenis->nama }} ({{ $jenis->usia_pemberian }})</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_imunisasi_id') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Keterangan / Efek</label>
                                    <input type="text" wire:model="keterangan_imunisasi" placeholder="cth: Paha kiri, kondisi sehat" class="w-full h-10 px-3 border rounded-xl text-sm bg-white">
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Vitamin A Checkbox & Sub-form --}}
                    <div class="p-3.5 rounded-xl border {{ $berikan_vitamin ? 'bg-purple-50/50 border-purple-200' : 'bg-slate-50 border-slate-200' }} transition-colors">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model.live="berikan_vitamin" class="w-4 h-4 text-purple-600 rounded">
                            <span class="text-sm font-bold text-slate-800">Pemberian Vitamin A Hari Ini</span>
                        </label>

                        @if($berikan_vitamin)
                            <div class="mt-3 pt-3 border-t border-purple-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kapsul <span class="text-red-500">*</span></label>
                                    <select wire:model="jenis_vitamin" class="w-full h-10 px-3 border rounded-xl text-sm bg-white">
                                        <option value="">— Pilih Kapsul —</option>
                                        <option value="kapsul_biru">Kapsul Biru (100.000 IU, 6-11 bulan)</option>
                                        <option value="kapsul_merah">Kapsul Merah (200.000 IU, 12-59 bulan)</option>
                                    </select>
                                    @error('jenis_vitamin') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Keterangan Tambahan</label>
                                    <input type="text" wire:model="keterangan_vitamin" placeholder="cth: Diberikan langsung" class="w-full h-10 px-3 border rounded-xl text-sm bg-white">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Button Simpan Pelayanan Lengkap --}}
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" wire:click="resetPelayananForm" class="text-xs text-slate-500 hover:text-slate-700">
                        Reset Isian
                    </button>
                    
                    <button type="button" wire:click="simpanPelayanan" 
                            class="inline-flex items-center gap-2 h-11 px-6 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl transition-all shadow-md active:scale-[0.98]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ $editingPenimbanganId ? 'Perbarui Data Pelayanan' : 'Simpan Pelayanan Hari Ini' }}
                    </button>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: PROFIL SASARAN & STATISTIK HARI INI (5 Kolom) --}}
        <div class="lg:span-5 lg:col-span-5 space-y-6">
            
            {{-- Profil Balita Aktif --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <h3 class="font-heading font-bold text-slate-800 text-base mb-3 border-b border-slate-100 pb-2">Profil Sasaran Terpilih</h3>
                
                @if($selectedAnak)
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl {{ $selectedAnak->jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600' }} flex items-center justify-center font-bold text-xl">
                                {{ $selectedAnak->jenis_kelamin }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-base">{{ $selectedAnak->nama }}</h4>
                                <p class="text-xs text-slate-500">Lahir: {{ $selectedAnak->tanggal_lahir->format('d M Y') }} ({{ $selectedAnak->usia }})</p>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl space-y-1.5 text-xs text-slate-700">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Nama Ibu:</span>
                                <span class="font-semibold">{{ $selectedAnak->ibu->nama ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">NIK Ibu:</span>
                                <span class="font-mono">{{ $selectedAnak->ibu->nik ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Alamat:</span>
                                <span class="text-right truncate max-w-[200px]">{{ $selectedAnak->ibu->alamat ?? '-' }}</span>
                            </div>
                            @if($selectedAnak->ibu && $selectedAnak->ibu->telepon)
                            <div class="flex justify-between">
                                <span class="text-slate-400">No HP/WA:</span>
                                <span>{{ $selectedAnak->ibu->telepon }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Riwayat Terakhir --}}
                        @php
                            $terakhir = $selectedAnak->penimbangan->sortByDesc('tanggal_pelayanan')->first();
                        @endphp
                        @if($terakhir)
                            <div class="p-3 bg-emerald-50/60 border border-emerald-100 rounded-xl text-xs text-emerald-800">
                                <p class="font-bold text-emerald-900 mb-1">Penimbangan Terakhir ({{ $terakhir->tanggal_pelayanan->format('d/m/Y') }}):</p>
                                <p>BB: <span class="font-bold">{{ $terakhir->berat_badan }} kg</span> · TB: <span class="font-bold">{{ $terakhir->tinggi_badan }} cm</span> · Status: <span class="font-bold capitalize">{{ $terakhir->status_bbu }}</span></p>
                            </div>
                        @endif

                        <div class="pt-2">
                            <a href="{{ route('penimbangan.grafik', $selectedAnak->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:underline">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                Lihat Grafik Pertumbuhan KMS Lengkap →
                            </a>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <p class="text-sm font-medium">Belum ada balita yang dipilih</p>
                        <p class="text-xs">Cari nama balita pada form di samping</p>
                    </div>
                @endif
            </div>

            {{-- Ringkasan Statistik Pelayanan Hari Ini --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <h3 class="font-heading font-bold text-slate-800 text-base mb-3 border-b border-slate-100 pb-2">Statistik Hari Ini ({{ Carbon::parse($tanggal_pelayanan)->locale('id')->isoFormat('D MMM Y') }})</h3>
                
                <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                    <div class="p-3 bg-primary-50 rounded-xl">
                        <p class="text-2xl font-bold text-primary-700">{{ $totalAnakHariIni }}</p>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase mt-0.5">Balita Ditimbang</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <p class="text-2xl font-bold text-blue-700">{{ $totalImunisasiHariIni }}</p>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase mt-0.5">Imunisasi</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl">
                        <p class="text-2xl font-bold text-purple-700">{{ $totalVitaminHariIni }}</p>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase mt-0.5">Vitamin A</p>
                    </div>
                </div>

                {{-- Status Gizi Ringkas Hari Ini --}}
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-600 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> Gizi Baik</span>
                        <span class="font-bold text-slate-800">{{ $giziHariIni['baik'] }} anak</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-600 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Gizi Kurang</span>
                        <span class="font-bold text-slate-800">{{ $giziHariIni['kurang'] }} anak</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-600 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Gizi Buruk</span>
                        <span class="font-bold text-slate-800">{{ $giziHariIni['buruk'] }} anak</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-600 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Gizi Lebih</span>
                        <span class="font-bold text-slate-800">{{ $giziHariIni['lebih'] }} anak</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- BAGIAN 4: TABEL REKAPITULASI PELAYANAN HARI INI (LANGSUNG DI BAWAHNYA) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-heading font-bold text-slate-800 text-lg">Daftar Balita Dilayani Hari Ini</h3>
                <p class="text-xs text-slate-400">Tanggal: {{ Carbon::parse($tanggal_pelayanan)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-700 rounded-full w-fit">
                Total: {{ $pelayananHariIni->count() }} Kunjungan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Balita</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Ibu</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">BB (kg)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">TB (cm)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status Gizi (BB/U)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Z-Score</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pelayananHariIni as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-bold text-slate-900">{{ $p->anak->nama ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $p->anak->usia ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->anak->ibu->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $p->berat_badan }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->tinggi_badan }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                    {{ $p->status_bbu === 'baik' ? 'bg-green-50 text-green-700' : ($p->status_bbu === 'kurang' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $p->status_bbu }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-500">{{ $p->zscore_bbu }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="editPelayanan({{ $p->id }})" class="text-xs font-semibold text-primary-600 hover:text-primary-800 bg-primary-50 px-3 py-1.5 rounded-lg transition-colors">
                                    Koreksi / Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <p class="text-sm font-medium">Belum ada pelayanan yang dicatat pada tanggal ini.</p>
                                <p class="text-xs">Gunakan form di atas untuk mulai mencatat balita yang hadir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
