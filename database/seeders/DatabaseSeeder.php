<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the initial production administrator account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@baifa.com.ve'],
            [
                'name' => 'Administrador BaiFa',
                'password' => Hash::make('12345678'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );
    }
}
