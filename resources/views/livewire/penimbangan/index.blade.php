<?php

use App\Models\Anak;
use App\Models\Penimbangan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

new #[Title('Penimbangan')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        Penimbangan::findOrFail($id)->delete();
        session()->flash('success', 'Data penimbangan berhasil dihapus.');
    }

    public function with(): array
    {
        $data = Penimbangan::with(['anak.ibu'])
            ->when($this->search, fn ($q) =>
                $q->whereHas('anak', fn ($q2) => $q2->where('nama', 'like', "%{$this->search}%"))
            )
            ->orderByDesc('tanggal_pelayanan')
            ->paginate(15);

        return [
            'dataPenimbangan' => $data,
        ];
    }
};
?>
<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-xl text-slate-900">Penimbangan</h2>
            <p class="text-sm text-slate-500">Riwayat dan pencatatan penimbangan anak</p>
        </div>
        <a href="{{ route('penimbangan.create') }}" class="inline-flex items-center gap-2 h-10 px-5 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama anak..."
               class="h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all sm:w-64">

    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Anak</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">BB (kg)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">TB (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">LK (cm)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status BB/U</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dataPenimbangan as $p)
                        @php
                            $badgeClass = match($p->status_bbu) {
                                'buruk'  => 'bg-red-50 text-red-700 border border-red-200',
                                'kurang' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                'baik'   => 'bg-green-50 text-green-700 border border-green-200',
                                'lebih'  => 'bg-blue-50 text-blue-700 border border-blue-200',
                                default  => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <tr class="hover:bg-primary-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->tanggal_pelayanan->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">
                                {{ $p->anak->nama }}
                                <span class="block text-xs text-slate-400 font-normal">{{ $p->anak->ibu->nama ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ $p->berat_badan }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->tinggi_badan }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->lingkar_kepala ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $p->label_status_bbu }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('penimbangan.grafik', $p->anak_id) }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Grafik KMS">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    </a>
                                    <a href="{{ route('penimbangan.edit', $p->id) }}" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <button wire:click="delete({{ $p->id }})" wire:confirm="Hapus data penimbangan ini?" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/></svg>
                                    <p class="font-medium text-slate-500">Belum ada data penimbangan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dataPenimbangan->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $dataPenimbangan->links() }}</div>
        @endif
    </div>
</div>
