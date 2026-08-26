<?php

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public ?User $user = null;
    
    public string $nama = '';
    public string $username = '';
    public string $password = '';
    public string $role = 'kader';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->user = User::findOrFail($id);
            $this->nama = $this->user->nama;
            $this->username = $this->user->username;
            $this->role = $this->user->role;
        }
    }

    public function save(): void
    {
        $rules = [
            'nama' => ['required', 'string', 'max:100'],
            'role' => ['required', 'in:admin,kader'],
        ];

        if ($this->user) {
            $rules['username'] = ['required', 'string', 'max:50', Rule::unique('users')->ignore($this->user->id)];
            $rules['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $rules['username'] = ['required', 'string', 'max:50', 'unique:users,username'];
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $validated = $this->validate($rules, [
            'nama.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($this->user) {
            $this->user->update($validated);
            session()->flash('success', 'User berhasil diperbarui.');
        } else {
            User::create($validated);
            session()->flash('success', 'User berhasil ditambahkan.');
        }

        $this->redirect(route('user-management.index'), navigate: true);
    }
};
?>
<div x-data>
    <div class="mb-6">
        <a href="{{ route('user-management.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-primary-600 transition-colors">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-heading font-bold text-lg text-slate-900">{{ $user ? 'Edit User' : 'Tambah User' }}</h2>
            <p class="text-sm text-slate-500">{{ $user ? 'Ubah data user' : 'Tambahkan user baru sebagai admin atau kader' }}</p>
        </div>

        <form wire:submit="save" class="p-6 space-y-5">
            <div>
                <label for="nama" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                <input wire:model="nama" type="text" id="nama" class="w-full h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all @error('nama') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('nama') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                <input wire:model="username" type="text" id="username" class="w-full h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all @error('username') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('username') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password {{ $user ? '(Biarkan kosong jika tidak ingin mengubah)' : '' }}</label>
                <input wire:model="password" type="password" id="password" class="w-full h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('password') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                <select wire:model="role" id="role" class="w-full h-11 px-4 border-[1.5px] border-slate-300 rounded-[10px] text-sm focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all bg-white @error('role') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="kader">Kader</option>
                    <option value="admin">Admin</option>
                </select>
                @error('role') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('user-management.index') }}" wire:navigate class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-[10px] transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center h-10 px-6 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
