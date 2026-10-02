<?php

namespace Database\Seeders;

use App\Enums\GeneratorStatus;
use App\Models\Client;
use App\Models\Generator;
use Illuminate\Database\Seeder;

class GeneratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $indVen = Client::where('rif', 'J-12345678-9')->first();
        $constAndina = Client::where('rif', 'J-98765432-1')->first();
        $agroLlanos = Client::where('rif', 'J-45678901-2')->first();

        $generators = [
            [
                'serial_number' => 'GEN-2026-0041',
                'name' => 'Generador Principal Planta Valencia',
                'model' => 'Cummins 150 kVA Silent Diésel',
                'capacity_kva' => 150.00,
                'status' => GeneratorStatus::IN_TRANSIT,
                'client_id' => $indVen?->id,
                'estimated_arrival_date' => now()->addDays(3)->format('Y-m-d'),
                'notes' => 'Despachado desde almacén central. Transporte Transporte Los Andes Placa A12BC3D.',
            ],
            [
                'serial_number' => 'GEN-2026-0038',
                'name' => 'Generador Auxiliar Stock',
                'model' => 'Perkins 60 kVA Insonorizado',
                'capacity_kva' => 60.00,
                'status' => GeneratorStatus::WAREHOUSE,
                'client_id' => null,
                'estimated_arrival_date' => null,
                'notes' => 'En inventario físico en Warehouse Principal Baifa Caracas.',
            ],
            [
                'serial_number' => 'GEN-2026-0029',
                'name' => 'Generador Obra Maracaibo',
                'model' => 'Baudouin 250 kVA Diésel Abierto',
                'capacity_kva' => 250.00,
                'status' => GeneratorStatus::CHECKPOINT,
                'client_id' => $constAndina?->id,
                'estimated_arrival_date' => now()->addDays(1)->format('Y-m-d'),
                'notes' => 'Detenido en Punto de Control Alcabala Guacara para revisión de guía de transporte.',
            ],
            [
                'serial_number' => 'GEN-2026-0015',
                'name' => 'Generador Hacienda Santa Inés',
                'model' => 'Weichai 100 kVA Silent',
                'capacity_kva' => 100.00,
                'status' => GeneratorStatus::DELIVERED,
                'client_id' => $agroLlanos?->id,
                'estimated_arrival_date' => now()->subDays(2)->format('Y-m-d'),
                'notes' => 'Recibido en finca por el encargado de operaciones. Pendiente arranque y conexionado.',
            ],
            [
                'serial_number' => 'GEN-2026-0008',
                'name' => 'Generador Respaldo Sede Fabril',
                'model' => 'Cummins 500 kVA Power Heavy',
                'capacity_kva' => 500.00,
                'status' => GeneratorStatus::INSTALLED,
                'client_id' => $indVen?->id,
                'estimated_arrival_date' => now()->subDays(15)->format('Y-m-d'),
                'notes' => 'Instalado, cableado y probado con transferencia automática 100% operativa.',
            ],
        ];

        foreach ($generators as $data) {
            Generator::updateOrCreate(
                ['serial_number' => $data['serial_number']],
                $data
            );
        }
    }
}
