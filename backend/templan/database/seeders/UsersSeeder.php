<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administradores
        User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@hemeracapital.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'status' => 'Ativo',
        ]);

        User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@hemeracapital.com',
            'password' => Hash::make('superadmin123'),
            'is_admin' => true,
            'status' => 'Ativo',
        ]);

        // Funcionários
        $funcionarios = [
            ['name' => 'João Silva', 'email' => 'joao.silva@hemeracapital.com'],
            ['name' => 'Maria Santos', 'email' => 'maria.santos@hemeracapital.com'],
            ['name' => 'Pedro Costa', 'email' => 'pedro.costa@hemeracapital.com'],
            ['name' => 'Ana Ferreira', 'email' => 'ana.ferreira@hemeracapital.com'],
            ['name' => 'Carlos Oliveira', 'email' => 'carlos.oliveira@hemeracapital.com'],
            ['name' => 'Sofia Rodrigues', 'email' => 'sofia.rodrigues@hemeracapital.com'],
        ];

        foreach ($funcionarios as $funcionario) {
            User::create([
                'name' => $funcionario['name'],
                'email' => $funcionario['email'],
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'status' => 'Pendente',
            ]);
        }
    }
}