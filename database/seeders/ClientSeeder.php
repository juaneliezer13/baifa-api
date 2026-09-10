<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'company_fiscal_name' => 'Industrias Venezolanas Toro S.A.',
                'company_short_name' => 'IndVen Toro',
                'rif' => 'J-12345678-9',
                'office_phone' => '0212-555-0100',
                'contact_name' => 'Carlos Mendoza',
                'contact_email' => 'cmendoza@indventoro.com.ve',
                'contact_phone' => '0414-555-0101',
                'is_active' => true,
            ],
            [
                'company_fiscal_name' => 'Constructora Andina C.A.',
                'company_short_name' => 'ConstAndina',
                'rif' => 'J-98765432-1',
                'office_phone' => '0261-444-0200',
                'contact_name' => 'María González',
                'contact_email' => 'mgonzalez@constructoraandina.com',
                'contact_phone' => '0424-555-0201',
                'is_active' => true,
            ],
            [
                'company_fiscal_name' => 'Agropecuaria Los Llanos S.R.L.',
                'company_short_name' => 'AgroLlanos',
                'rif' => 'J-45678901-2',
                'office_phone' => '0243-333-0300',
                'contact_name' => 'Roberto Díaz',
                'contact_email' => 'rdiaz@agrollanos.com.ve',
                'contact_phone' => '0416-555-0301',
                'is_active' => true,
            ],
            [
                'company_fiscal_name' => 'Distribuidora Nacional de Alimentos C.A.',
                'company_short_name' => 'DiNaCa',
                'rif' => 'J-31416926-7',
                'office_phone' => '0212-777-0400',
                'contact_name' => 'Ana Torres',
                'contact_email' => 'atorres@dinaca.com.ve',
                'contact_phone' => '0412-555-0401',
                'is_active' => false,
            ],
        ];

        foreach ($clients as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['contact_email']],
                [
                    'name' => $data['contact_name'],
                    'password' => Hash::make('12345678'),
                    'role' => UserRole::CLIENT,
                    'is_active' => $data['is_active'],
                ]
            );

            $data['user_id'] = $user->id;

            $client = Client::firstOrNew(['rif' => $data['rif']]);
            $client->fill($data);
            $client->user_id = $user->id;
            $client->save();
        }
    }
}
