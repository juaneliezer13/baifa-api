<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_list_clients(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/clients');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_client_and_user_is_automatically_created_with_client_role(): void
    {
        $payload = [
            'company_fiscal_name' => 'Corporación Industrial Futuro C.A.',
            'company_short_name' => 'Corp Futuro',
            'rif' => 'J-12345678-9',
            'office_phone' => '0212-555-1234',
            'contact_name' => 'Alejandro Ramos',
            'contact_email' => 'aramos@corpfuturo.com.ve',
            'contact_phone' => '0414-111-2233',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/clients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('client.company_short_name', 'Corp Futuro')
            ->assertJsonPath('client.contact_email', 'aramos@corpfuturo.com.ve')
            ->assertJsonPath('client.user.email', 'aramos@corpfuturo.com.ve');

        // Check user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Alejandro Ramos',
            'email' => 'aramos@corpfuturo.com.ve',
            'role' => 'client',
            'is_active' => true,
        ]);

        // Check client was created and linked to user
        $user = User::where('email', 'aramos@corpfuturo.com.ve')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('clients', [
            'rif' => 'J-12345678-9',
            'user_id' => $user->id,
        ]);
    }

    public function test_client_creation_fails_if_contact_email_already_exists_in_users(): void
    {
        User::factory()->create([
            'email' => 'existing@empresa.com',
        ]);

        $payload = [
            'company_fiscal_name' => 'Empresa Duplicada S.A.',
            'company_short_name' => 'Empresa Dup',
            'rif' => 'J-99887766-5',
            'contact_name' => 'Contacto Dup',
            'contact_email' => 'existing@empresa.com',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/clients', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contact_email']);
    }

    public function test_user_cannot_be_created_with_client_role_via_users_endpoint(): void
    {
        $payload = [
            'name' => 'Direct Client User',
            'email' => 'directclient@test.com',
            'password' => '12345678',
            'role' => 'client',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role'])
            ->assertJsonPath('errors.role.0', 'Los usuarios de tipo cliente solo pueden crearse a través del módulo de clientes.');
    }

    public function test_client_user_role_cannot_be_changed_via_users_endpoint(): void
    {
        $clientUser = User::factory()->create([
            'role' => UserRole::CLIENT,
        ]);

        $payload = [
            'role' => 'admin',
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/v1/users/{$clientUser->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role'])
            ->assertJsonPath('errors.role.0', 'No se puede cambiar el rol a un usuario de tipo cliente.');
    }

    public function test_updating_client_contact_email_syncs_to_user_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Pedro Pérez',
            'email' => 'old_email@test.com',
            'role' => UserRole::CLIENT,
        ]);

        $client = Client::create([
            'company_fiscal_name' => 'Compañía Vieja C.A.',
            'company_short_name' => 'CompVieja',
            'rif' => 'J-11112222-3',
            'contact_name' => 'Pedro Pérez',
            'contact_email' => 'old_email@test.com',
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $updatePayload = [
            'contact_email' => 'new_email@test.com',
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/v1/clients/{$client->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('client.contact_email', 'new_email@test.com')
            ->assertJsonPath('client.user.email', 'new_email@test.com');

        $this->assertEquals('new_email@test.com', $user->fresh()->email);
    }

    public function test_updating_client_user_email_syncs_to_client_contact_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Laura Gómez',
            'email' => 'laura_old@test.com',
            'role' => UserRole::CLIENT,
        ]);

        $client = Client::create([
            'company_fiscal_name' => 'Laura S.A.',
            'company_short_name' => 'LauraSA',
            'rif' => 'J-33334444-5',
            'contact_name' => 'Laura Gómez',
            'contact_email' => 'laura_old@test.com',
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $updatePayload = [
            'email' => 'laura_new@test.com',
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/v1/users/{$user->id}", $updatePayload);

        $response->assertStatus(200);
        $this->assertEquals('laura_new@test.com', $client->fresh()->contact_email);
    }

    public function test_toggle_status_syncs_between_client_and_user(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $client = Client::create([
            'company_fiscal_name' => 'Status Test C.A.',
            'company_short_name' => 'StatusTest',
            'rif' => 'J-55556666-7',
            'contact_name' => 'Status Contact',
            'contact_email' => 'status@test.com',
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/api/v1/clients/{$client->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJsonPath('client.is_active', false);

        $this->assertFalse($client->fresh()->is_active);
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_deleting_client_also_deletes_associated_user(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);
        $user->createToken('test_token');

        $client = Client::create([
            'company_fiscal_name' => 'Delete Test C.A.',
            'company_short_name' => 'DeleteTest',
            'rif' => 'J-77778888-9',
            'contact_name' => 'Delete Contact',
            'contact_email' => 'delete@test.com',
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(200);

        // Client soft deleted
        $this->assertSoftDeleted('clients', ['id' => $client->id]);

        // User deleted from database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_cannot_be_deleted_directly_if_it_is_a_client_user(): void
    {
        $clientUser = User::factory()->create([
            'role' => UserRole::CLIENT,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/users/{$clientUser->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No se puede eliminar directamente un usuario de tipo cliente. Para poder eliminar este usuario, debe eliminar primero la empresa cliente asociada desde el módulo de Clientes.');

        $this->assertDatabaseHas('users', ['id' => $clientUser->id]);
    }
}
