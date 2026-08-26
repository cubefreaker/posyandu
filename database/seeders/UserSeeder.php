<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'nama' => 'Administrator',
            'role' => 'admin',
        ]);

        User::create([
            'username' => 'kader',
            'password' => Hash::make('kader123'),
            'nama' => 'Kader Posyandu',
            'role' => 'kader',
        ]);
    }
}
