<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@baifa.com.ve'],
            [
                'name' => 'Adriana Morales',
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                'role' => \App\Enums\UserRole::ADMIN,
            ]
        );

        $this->call(ClientSeeder::class);

    }
}
