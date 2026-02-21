<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin',
            'email' => 'admin@example.com',
            'phone' => '0123456789',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
    }
}
