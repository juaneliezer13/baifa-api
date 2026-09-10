<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Administrador principal (Adriana Morales)
        User::updateOrCreate(
            ['email' => 'admin@baifa.com.ve'],
            [
                'name' => 'Adriana Morales',
                'password' => Hash::make('12345678'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );

        // 2. Administrador alternativo / corto
        User::updateOrCreate(
            ['email' => 'admin@admin'],
            [
                'name' => 'Admin Baifa',
                'password' => Hash::make('12345678'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );

        // 3. Jefe / Gerente
        User::updateOrCreate(
            ['email' => 'gerente@baifa.com.ve'],
            [
                'name' => 'Fernando Soto',
                'password' => Hash::make('12345678'),
                'role' => UserRole::MANAGER,
                'is_active' => true,
            ]
        );

        // 4. Operador / Empleado
        User::updateOrCreate(
            ['email' => 'operador@baifa.com.ve'],
            [
                'name' => 'Luis Ramírez',
                'password' => Hash::make('12345678'),
                'role' => UserRole::EMPLOYEE,
                'is_active' => true,
            ]
        );

        // 5. Cliente demo institucional
        $demoClientUser = User::updateOrCreate(
            ['email' => 'cliente.real@empresa.com'],
            [
                'name' => 'Cliente Real Demo',
                'password' => Hash::make('12345678'),
                'role' => UserRole::CLIENT,
                'is_active' => true,
            ]
        );

        Client::updateOrCreate(
            ['rif' => 'J-11223344-5'],
            [
                'company_fiscal_name' => 'Empresa Cliente Real S.A.',
                'company_short_name' => 'Cliente Real',
                'contact_name' => 'Cliente Real Demo',
                'contact_email' => 'cliente.real@empresa.com',
                'office_phone' => '0212-999-0000',
                'contact_phone' => '0414-999-0001',
                'is_active' => true,
                'user_id' => $demoClientUser->id,
            ]
        );

        // 6. Sembrar directorio de clientes base de Figma
        $this->call(ClientSeeder::class);
    }
}
