<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Tan SuperAdmin',
            'email' => 'tan@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_SUPERADMIN,
        ]);

        User::create([
            'name' => 'Admin 1',
            'email' => 'admin@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_ADMIN,
        ]);

        User::create([
            'name' => 'Test Customer',
            'email' => 'exp@cloth.com',
            'password' => Hash::make('asd123'),
            'role' => User::ROLE_USER,
        ]);
    }
}
