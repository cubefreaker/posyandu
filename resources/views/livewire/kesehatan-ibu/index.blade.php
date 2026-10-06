<?php

use App\Models\Kehamilan;
use App\Models\Ibu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

new #[Title('Kesehatan Ibu Hamil (Buku KIA)')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'aktif'; // aktif, semua, melahirkan

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Hanya admin yang berhak menghapus data kehamilan.');
            return;
        }

        Kehamilan::findOrFail($id)->delete();
        session()->flash('success', 'Data kehamilan berhasil dihapus.');
    }

    public function with(): array
    {
        $query = Kehamilan::with(['ibu', 'pemeriksaan'])
            ->when($this->search, fn ($q) =>
                $q->whereHas('ibu', fn ($q2) => $q2->where('nama', 'like', "%{$this->search}%")->orWhere('nik', 'like', "%{$this->search}%"))
            );

        if ($this->filterStatus !== 'semua') {
            $query->where('status_kehamilan', $this->filterStatus);
        }

        $dataKehamilan = $query->latest()->paginate(10);

        // Statistik Cepat
        $totalIbuHamilAktif = Kehamilan::where('status_kehamilan', 'aktif')->count();
        $totalRisikoKek = Kehamilan::where('status_kehamilan', 'aktif')->where('status_kek', true)->count();
        $totalTrimester3 = Kehamilan::where('status_kehamilan', 'aktif')->get()->filter(fn ($k) => $k->usia_minggu >= 28)->count();

        return compact('dataKehamilan', 'totalIbuHamilAktif', 'totalRisikoKek', 'totalTrimester3');
    }
};
?>

<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-pink-100 text-pink-600 rounded-xl">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                </span>
                <div>
                    <h2 class="font-heading font-bold text-xl text-slate-900">Kesehatan Ibu Hamil (Buku KIA)</h2>
                    <p class="text-sm text-slate-500">Pemantauan Antenatal Care (ANC), skrining KEK, tensi, dan grafik kenaikan BB ibu hamil</p>
                </div>
            </div>
        </div>

        <a href="{{ route('kesehatan-ibu.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-5 bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm rounded-xl transition-all shadow-xs active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + Registrasi Ibu Hamil
        </a>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-lg">
                🤰
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ibu Hamil Aktif</p>
                <p class="text-2xl font-bold text-slate-900">{{ $totalIbuHamilAktif }}</p>
                <p class="text-[11px] text-slate-500">dalam pemantauan posyandu</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                ⚠️
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Risiko KEK (LiLA &lt; 23.5)</p>
                <p class="text-2xl font-bold text-amber-600">{{ $totalRisikoKek }}</p>
                <p class="text-[11px] text-slate-500">perlu asupan gizi & PMT bumil</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                📅
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mendekati HPL (Trimester 3)</p>
                <p class="text-2xl font-bold text-blue-600">{{ $totalTrimester3 }}</p>
                <p class="text-[11px] text-slate-500">usia kehamilan &ge; 28 minggu</p>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama ibu atau NIK..."
               class="w-full sm:w-80 h-10 px-4 border-[1.5px] border-slate-300 rounded-xl text-sm placeholder:text-slate-400 focus:border-pink-500 focus:ring-[3px] focus:ring-pink-500/20 outline-none transition-all">

        <div class="grid grid-cols-3 gap-1.5 w-full sm:w-auto sm:flex sm:gap-2 text-center">
            <button wire:click="$set('filterStatus', 'aktif')" class="px-2.5 sm:px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filterStatus === 'aktif' ? 'bg-pink-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                Kehamilan Aktif
            </button>
            <button wire:click="$set('filterStatus', 'melahirkan')" class="px-2.5 sm:px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filterStatus === 'melahirkan' ? 'bg-pink-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                Melahirkan
            </button>
            <button wire:click="$set('filterStatus', 'semua')" class="px-2.5 sm:px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filterStatus === 'semua' ? 'bg-pink-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                Semua
            </button>
        </div>
    </div>

    {{-- Tabel Ibu Hamil --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[850px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Ibu Hamil</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kehamilan (G)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Usia Gestasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Perkiraan Lahir (HPL)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">IMT Pra-Hamil</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status KEK</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kunjungan ANC</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dataKehamilan as $k)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-bold text-slate-900">{{ $k->ibu->nama ?? '-' }}</p>
                                <p class="text-xs text-slate-400 font-mono">NIK: {{ $k->ibu->nik ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-800">
                                G{{ $k->kehamilan_ke }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-bold text-pink-600">{{ $k->usia_minggu }} Minggu</span>
                                <span class="block text-[11px] text-slate-400">Trimester {{ $k->trimester_saat_ini }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $k->hpl ? $k->hpl->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold text-slate-800">{{ $k->imt_pra_hamil }}</span>
                                <span class="block text-[10px] text-slate-400 capitalize">{{ $k->label_kategori_imt }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($k->status_kek)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Risiko KEK ({{ $k->lila_awal }} cm)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-50 text-green-700">
                                        Normal ({{ $k->lila_awal ?? '-' }} cm)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-slate-700">
                                {{ $k->pemeriksaan->count() }} kali periksa
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Tombol Periksa ANC --}}
                                    <a href="{{ route('kesehatan-ibu.periksa', $k->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 rounded-lg text-xs font-bold transition-colors" title="Input Pemeriksaan ANC">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                        Periksa
                                    </a>

                                    {{-- Tombol Grafik Kenaikan BB Buku KIA --}}
                                    <a href="{{ route('kesehatan-ibu.grafik', $k->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-colors" title="Lihat Grafik Buku KIA">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                        Grafik KIA
                                    </a>

                                    {{-- Edit Profil Kehamilan --}}
                                    <a href="{{ route('kesehatan-ibu.edit', $k->id) }}" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg transition-colors" title="Edit Profil">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    {{-- Hapus (Hanya Admin) --}}
                                    @if(auth()->user()->role === 'admin')
                                    <button wire:click="delete({{ $k->id }})" wire:confirm="Hapus data kehamilan ibu '{{ $k->ibu->nama ?? '' }}'?" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                <p class="text-sm font-medium">Belum ada data ibu hamil</p>
                                <p class="text-xs">Klik tombol "+ Registrasi Ibu Hamil" untuk menambahkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dataKehamilan->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $dataKehamilan->links() }}
            </div>
        @endif
    </div>
</div>
