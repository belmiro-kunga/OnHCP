<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update an admin user
        $user = User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'secret123')),
                'is_admin' => true,
                'status' => 'Ativo',
                'mfa_enabled' => false,
            ]
        );

        // Optionally ensure password is hashed even if record existed
        if (!Hash::check(env('ADMIN_PASSWORD', 'secret123'), $user->password)) {
            $user->password = Hash::make(env('ADMIN_PASSWORD', 'secret123'));
            $user->save();
        }
    }
}
