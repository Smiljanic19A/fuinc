<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default superadmin user
        User::updateOrCreate([
            'email' => 'superadmin@example.com'
        ], [
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('SuperAdmin123!'),
            'user_type' => 'superadmin',
            'promoted_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Create a regular user for testing
        User::updateOrCreate([
            'email' => 'user@example.com'
        ], [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('User123!'),
            'user_type' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->command->info('SuperAdmin and regular user created successfully!');
        $this->command->info('SuperAdmin: superadmin@example.com / SuperAdmin123!');
        $this->command->info('Regular User: user@example.com / User123!');
    }
}
