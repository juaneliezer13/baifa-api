<?php

namespace Tests\Feature;

use App\Enums\GeneratorStatus;
use App\Enums\UserRole;
use App\Models\Checkpoint;
use App\Models\Client;
use App\Models\Generator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $clientUser;

    private User $adminUser;

    private Generator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear usuario administrador
        $this->adminUser = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        // 2. Crear usuario cliente y registro fiscal asociado
        $this->clientUser = User::factory()->create([
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $this->client = Client::create([
            'user_id' => $this->clientUser->id,
            'company_fiscal_name' => 'Inversiones Los Andes C.A.',
            'company_short_name' => 'Los Andes',
            'rif' => 'J-30987654-1',
            'contact_name' => 'Carlos Mendoza',
            'contact_email' => $this->clientUser->email,
            'contact_phone' => '0414-9988776',
            'is_active' => true,
        ]);

        // 3. Crear generador asignado al cliente
        $this->generator = Generator::create([
            'serial_number' => 'GEN-2026-0038',
            'client_id' => $this->client->id,
            'name' => 'Generador Planta Principal',
            'model' => 'BF-C250',
            'capacity_kva' => 250,
            'status' => GeneratorStatus::IN_TRANSIT,
            'estimated_arrival_date' => '2026-10-25',
            'notes' => 'Nota interna: Revisión en puerto completada.',
        ]);

        // 4. Crear punto de control con notas y usuario operador
        Checkpoint::create([
            'generator_id' => $this->generator->id,
            'user_id' => $this->adminUser->id,
            'status' => GeneratorStatus::IN_TRANSIT,
            'checkpoint_name' => 'Salida de Puerto La Guaira',
            'event_date' => now()->subDay(),
            'notes' => 'Salida en gandola refrigerada autorizada.',
        ]);
    }

    public function test_guest_unauthenticated_can_track_generator_with_limited_public_data(): void
    {
        $response = $this->getJson('/api/v1/tracking/GEN-2026-0038');

        $response->assertStatus(200)
            ->assertJsonPath('generator.serial_number', 'GEN-2026-0038')
            ->assertJsonPath('generator.model', 'BF-C250')
            ->assertJsonPath('generator.status', 'in_transit')
            ->assertJsonPath('generator.status_label', 'En Tránsito')
            ->assertJsonPath('generator.is_public_view', true)
            ->assertJsonPath('generator.requires_auth_for_details', true)
            ->assertJsonMissingPath('generator.client')
            ->assertJsonMissingPath('generator.notes');

        // Verificar que los puntos de control públicos no revelen el usuario operador ni datos sensibles
        $checkpoints = $response->json('generator.checkpoints');
        $this->assertCount(1, $checkpoints);
        $this->assertEquals('Salida de Puerto La Guaira', $checkpoints[0]['checkpoint_name']);
        $this->assertArrayNotHasKey('user_id', $checkpoints[0]);
        $this->assertArrayNotHasKey('changed_by', $checkpoints[0]);
    }

    public function test_authenticated_staff_gets_full_generator_tracking_data(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/tracking/GEN-2026-0038');

        $response->assertStatus(200)
            ->assertJsonPath('generator.serial_number', 'GEN-2026-0038')
            ->assertJsonPath('generator.is_public_view', false)
            ->assertJsonPath('generator.requires_auth_for_details', false)
            ->assertJsonPath('generator.client.company_short_name', 'Los Andes')
            ->assertJsonPath('generator.notes', 'Nota interna: Revisión en puerto completada.');

        $checkpoints = $response->json('generator.checkpoints');
        $this->assertCount(1, $checkpoints);
        $this->assertEquals($this->adminUser->name, $checkpoints[0]['changed_by']);
    }

    public function test_authenticated_owner_client_gets_full_generator_tracking_data(): void
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson('/api/v1/tracking/GEN-2026-0038');

        $response->assertStatus(200)
            ->assertJsonPath('generator.serial_number', 'GEN-2026-0038')
            ->assertJsonPath('generator.is_public_view', false)
            ->assertJsonPath('generator.requires_auth_for_details', false)
            ->assertJsonPath('generator.client.company_short_name', 'Los Andes');
    }

    public function test_authenticated_different_client_gets_limited_public_data(): void
    {
        $otherClientUser = User::factory()->create([
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);
        Client::create([
            'user_id' => $otherClientUser->id,
            'company_fiscal_name' => 'Otra Empresa C.A.',
            'company_short_name' => 'Otra Empresa',
            'rif' => 'J-44332211-0',
            'contact_name' => 'Pedro Pérez',
            'contact_email' => $otherClientUser->email,
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherClientUser, 'sanctum')
            ->getJson('/api/v1/tracking/GEN-2026-0038');

        $response->assertStatus(200)
            ->assertJsonPath('generator.serial_number', 'GEN-2026-0038')
            ->assertJsonPath('generator.is_public_view', true)
            ->assertJsonPath('generator.requires_auth_for_details', true)
            ->assertJsonMissingPath('generator.client');
    }

    public function test_tracking_nonexistent_serial_returns_404(): void
    {
        $response = $this->getJson('/api/v1/tracking/GEN-NONEXISTENT-999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'No se encontró ningún generador registrado con el serial: GEN-NONEXISTENT-999');
    }
}
