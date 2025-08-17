<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HemeraUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Administradores
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@hemeracapital.com',
                'password' => 'admin123',
                'is_admin' => true,
            ],
            [
                'name' => 'Super Administrador',
                'email' => 'superadmin@hemeracapital.com',
                'password' => 'superadmin123',
                'is_admin' => true,
            ],
            // Funcionários (mesma senha: password123)
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'Pedro Costa',
                'email' => 'pedro.costa@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'Ana Ferreira',
                'email' => 'ana.ferreira@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'Carlos Oliveira',
                'email' => 'carlos.oliveira@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
            [
                'name' => 'Sofia Rodrigues',
                'email' => 'sofia.rodrigues@hemeracapital.com',
                'password' => 'password123',
                'is_admin' => false,
            ],
        ];

        foreach ($users as $u) {
            User::query()->updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'is_admin' => $u['is_admin'],
                    'status' => 'Ativo',
                    'mfa_enabled' => false,
                ]
            );
        }
    }
}
