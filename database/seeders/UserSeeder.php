<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun bawaan. Role ditentukan di sini (lewat seeder), bukan dari form registrasi.
     * Password semua akun: password
     */
    public function run(): void
    {
        $akun = [
            ['Administrator', 'admin@gmail.com', 'admin'],
            ['Petugas Perpustakaan', 'petugas@gmail.com', 'petugas'],
            ['Siswa Teladan', 'siswa@gmail.com', 'siswa'],
            ['Siswa Kedua', 'siswa2@gmail.com', 'siswa'],
        ];

        foreach ($akun as [$nama, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => Hash::make('password')]
            );

            $user->syncRoles([$role]);
        }
    }
}
