<?php

use App\Models\Imunisasi;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

new #[Title('Imunisasi')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        Imunisasi::findOrFail($id)->delete();
        session()->flash('success', 'Data imunisasi berhasil dihapus.');
    }

    public function with(): array
    {
        $data = Imunisasi::with(['anak', 'jenisImunisasi'])
            ->when($this->search, fn ($q) =>
                $q->whereHas('anak', fn ($q2) => $q2->where('nama', 'like', "%{$this->search}%"))
                  ->orWhereHas('jenisImunisasi', fn ($q2) => $q2->where('nama', 'like', "%{$this->search}%"))
            )
            ->orderByDesc('tanggal_imunisasi')
            ->paginate(15);

        return ['dataImunisasi' => $data];
    }
};
?>
<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div><h2 class="font-heading font-bold text-xl text-slate-900">Imunisasi</h2><p class="text-sm text-slate-500">Pencatatan riwayat imunisasi anak</p></div>
        <a href="{{ route('imunisasi.create') }}" class="inline-flex items-center gap-2 h-10 px-5 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Tambah
        </a>
    </div>
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama anak atau jenis imunisasi..."
               class="h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all sm:w-72">
    </div>
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Anak</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Imunisasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usia Pemberian</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dataImunisasi as $item)
                        <tr class="hover:bg-primary-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->tanggal_imunisasi->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $item->anak->nama ?? '-' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $item->jenisImunisasi->nama ?? '-' }}</span></td>
                            <td class="px-4 py-3 text-sm text-slate-500">{{ $item->jenisImunisasi->usia_pemberian ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500">{{ $item->keterangan ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('imunisasi.edit', $item->id) }}" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus data imunisasi ini?" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4"/><path d="m17 7 3-3"/><path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/></svg>
                            <p class="font-medium text-slate-500">Belum ada data imunisasi</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dataImunisasi->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $dataImunisasi->links() }}</div>@endif
    </div>
</div>
