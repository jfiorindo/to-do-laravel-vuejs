<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@teste.com'],
            [
                'name' => 'Admin Teste',
                'email' => 'admin@teste.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        // Usuário comum
        User::updateOrCreate(
            ['email' => 'usuario@teste.com'],
            [
                'name' => 'Usuário Teste',
                'email' => 'usuario@teste.com',
                'password' => Hash::make('usuario123'),
                'is_admin' => false,
            ]
        );
    }
    
}
