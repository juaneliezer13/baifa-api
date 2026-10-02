<?php

namespace Database\Seeders;

use App\Enums\GeneratorStatus;
use App\Models\Checkpoint;
use App\Models\Generator;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operador@baifa.com.ve')->first()
            ?? User::where('email', 'admin@baifa.com.ve')->first();
        $manager = User::where('email', 'gerente@baifa.com.ve')->first()
            ?? $operator;

        $checkpointsBySerial = [
            'GEN-2026-0041' => [
                [
                    'status' => GeneratorStatus::WAREHOUSE,
                    'checkpoint_name' => 'Almacén Central Caracas',
                    'event_date' => now()->subDays(5)->setTime(8, 30),
                    'notes' => 'Inspección técnica de pre-entrega aprobada. Generador preparado para despacho.',
                    'user_id' => $manager?->id,
                ],
                [
                    'status' => GeneratorStatus::IN_TRANSIT,
                    'checkpoint_name' => 'Distribuidor La Encrucijada',
                    'event_date' => now()->subDays(3)->setTime(11, 45),
                    'notes' => 'En ruta hacia Valencia con Transporte Los Andes Placa A12BC3D. Chofer: José Parra.',
                    'user_id' => $operator?->id,
                ],
                [
                    'status' => GeneratorStatus::IN_TRANSIT,
                    'checkpoint_name' => 'Peaje Guacara',
                    'event_date' => now()->subDays(1)->setTime(16, 20),
                    'notes' => 'Paso de peaje sin novedades mecánicas. Velocidad crucero 60 km/h.',
                    'user_id' => $operator?->id,
                ],
            ],
            'GEN-2026-0029' => [
                [
                    'status' => GeneratorStatus::WAREHOUSE,
                    'checkpoint_name' => 'Almacén Central Caracas',
                    'event_date' => now()->subDays(7)->setTime(9, 0),
                    'notes' => 'Carga y trincaje en plataforma de carga pesada.',
                    'user_id' => $manager?->id,
                ],
                [
                    'status' => GeneratorStatus::IN_TRANSIT,
                    'checkpoint_name' => 'Autopista Regional del Centro Km 84',
                    'event_date' => now()->subDays(4)->setTime(14, 15),
                    'notes' => 'Tránsito normal con custodia hacia el occidente del país.',
                    'user_id' => $operator?->id,
                ],
                [
                    'status' => GeneratorStatus::CHECKPOINT,
                    'checkpoint_name' => 'Punto de Control Alcabala Guacara',
                    'event_date' => now()->subDays(1)->setTime(18, 30),
                    'notes' => 'Detenido en Punto de Control Alcabala Guacara para revisión de guía de transporte e inspección de seriales.',
                    'user_id' => $operator?->id,
                ],
            ],
            'GEN-2026-0015' => [
                [
                    'status' => GeneratorStatus::WAREHOUSE,
                    'checkpoint_name' => 'Almacén Central Caracas',
                    'event_date' => now()->subDays(10)->setTime(10, 0),
                    'notes' => 'Equipo verificado en banco de prueba con carga resistiva al 100%.',
                    'user_id' => $manager?->id,
                ],
                [
                    'status' => GeneratorStatus::IN_TRANSIT,
                    'checkpoint_name' => 'Troncal 5 Tramo Barinas',
                    'event_date' => now()->subDays(5)->setTime(13, 0),
                    'notes' => 'Transporte avanzando hacia los llanos occidentales.',
                    'user_id' => $operator?->id,
                ],
                [
                    'status' => GeneratorStatus::DELIVERED,
                    'checkpoint_name' => 'Hacienda Santa Inés (Locación Final)',
                    'event_date' => now()->subDays(2)->setTime(16, 45),
                    'notes' => 'Recibido en finca por el encargado de operaciones. Pendiente arranque y conexionado.',
                    'user_id' => $operator?->id,
                ],
            ],
            'GEN-2026-0008' => [
                [
                    'status' => GeneratorStatus::WAREHOUSE,
                    'checkpoint_name' => 'Warehouse Principal Baifa',
                    'event_date' => now()->subDays(30)->setTime(9, 15),
                    'notes' => 'Ingreso a inventario y nacionalización de planta 500 kVA.',
                    'user_id' => $manager?->id,
                ],
                [
                    'status' => GeneratorStatus::IN_TRANSIT,
                    'checkpoint_name' => 'Distribuidor San Blas Valencia',
                    'event_date' => now()->subDays(20)->setTime(11, 30),
                    'notes' => 'Traslado especial escoltado hasta zona industrial.',
                    'user_id' => $operator?->id,
                ],
                [
                    'status' => GeneratorStatus::DELIVERED,
                    'checkpoint_name' => 'Planta Fabril Valencia',
                    'event_date' => now()->subDays(16)->setTime(15, 0),
                    'notes' => 'Descarga sobre losa de concreto antivibratoria.',
                    'user_id' => $operator?->id,
                ],
                [
                    'status' => GeneratorStatus::INSTALLED,
                    'checkpoint_name' => 'Sede Fabril Valencia (Sala de Máquinas)',
                    'event_date' => now()->subDays(15)->setTime(17, 30),
                    'notes' => 'Instalado, cableado y probado con transferencia automática 100% operativa.',
                    'user_id' => $operator?->id,
                ],
            ],
            'GEN-2026-0038' => [
                [
                    'status' => GeneratorStatus::WAREHOUSE,
                    'checkpoint_name' => 'Warehouse Principal Baifa Caracas',
                    'event_date' => now()->subDays(12)->setTime(10, 0),
                    'notes' => 'En inventario físico en Warehouse Principal Baifa Caracas.',
                    'user_id' => $manager?->id,
                ],
            ],
        ];

        foreach ($checkpointsBySerial as $serial => $checkpoints) {
            $generator = Generator::where('serial_number', $serial)->first();
            if (! $generator) {
                continue;
            }

            foreach ($checkpoints as $cpData) {
                Checkpoint::updateOrCreate(
                    [
                        'generator_id' => $generator->id,
                        'checkpoint_name' => $cpData['checkpoint_name'],
                        'status' => $cpData['status']->value,
                    ],
                    [
                        'event_date' => $cpData['event_date'],
                        'notes' => $cpData['notes'],
                        'user_id' => $cpData['user_id'],
                    ]
                );
            }
        }
    }
}
