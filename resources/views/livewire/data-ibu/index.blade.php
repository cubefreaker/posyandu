<?php

use App\Models\Ibu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

new #[Title('Data Ibu')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak. Hanya admin yang dapat menghapus data.');
            return;
        }

        $ibu = Ibu::findOrFail($id);
        if ($ibu->anak()->count() > 0) {
            session()->flash('error', 'Data ibu tidak bisa dihapus karena masih memiliki anak terdaftar.');
            return;
        }
        $ibu->delete();
        session()->flash('success', 'Data ibu berhasil dihapus.');
    }

    public function with(): array
    {
        $data = Ibu::withCount('anak')
            ->when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%")->orWhere('nik', 'like', "%{$this->search}%"))
            ->orderBy('nama')
            ->paginate(10);

        return ['dataIbu' => $data];
    }
};
?>
<div>
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-xl text-slate-900">Data Ibu</h2>
            <p class="text-sm text-slate-500">Kelola data ibu/wali anak posyandu</p>
        </div>
        <a href="{{ route('data-ibu.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-5 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97] w-full sm:w-auto">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Data
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau NIK..."
               class="w-full sm:max-w-sm h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Ibu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Lahir</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Anak</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dataIbu as $ibu)
                        <tr class="hover:bg-primary-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-mono text-slate-600">{{ $ibu->nik }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $ibu->nama }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $ibu->tanggal_lahir->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 max-w-48 truncate">{{ $ibu->alamat }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ibu->anak_count > 0 ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $ibu->anak_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('data-ibu.edit', $ibu->id) }}" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                    <button wire:click="delete({{ $ibu->id }})" wire:confirm="Yakin ingin menghapus data ibu '{{ $ibu->nama }}'?" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <p class="font-medium text-slate-500">Belum ada data ibu</p>
                                    <p class="text-sm">Mulai dengan menambahkan data ibu pertama</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dataIbu->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $dataIbu->links() }}
            </div>
        @endif
    </div>
</div>
