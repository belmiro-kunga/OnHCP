<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create initial admin user
        $this->call(AdminUserSeeder::class);
        
        // Create security data (IP policies and audit logs)
        $this->call(SecuritySeeder::class);
        
        // Create authentication and access data (role mappings, permissions, delegations, licenses)
        $this->call(AuthAccessSeeder::class);
    }
}
