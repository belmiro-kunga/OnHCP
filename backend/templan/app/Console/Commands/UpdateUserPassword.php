<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword extends Command
{
    protected $signature = 'user:update-password {email} {password}';
    protected $description = 'Update user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found');
            return 1;
        }
        
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info('Password updated successfully');
        return 0;
    }
}