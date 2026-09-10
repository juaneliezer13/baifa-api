<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_client_with_company_and_rif(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Juan Chirinos',
            'email' => 'juan@example.com',
            'company_fiscal_name' => 'Inversiones Alpha C.A.',
            'company_short_name' => 'Alpha',
            'rif' => 'J-12345678-9',
            'phone' => '0212-5551234',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role', 'role_label', 'created_at'],
                'client' => ['id', 'company_fiscal_name', 'company_short_name', 'rif', 'contact_name', 'contact_email'],
            ])
            ->assertJsonPath('user.role', 'client')
            ->assertJsonPath('user.role_label', 'Cliente')
            ->assertJsonPath('client.company_fiscal_name', 'Inversiones Alpha C.A.')
            ->assertJsonPath('client.rif', 'J-12345678-9');

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'role' => 'client',
        ]);

        $this->assertDatabaseHas('clients', [
            'company_fiscal_name' => 'Inversiones Alpha C.A.',
            'rif' => 'J-12345678-9',
            'contact_email' => 'juan@example.com',
        ]);

        $client = Client::where('rif', 'J-12345678-9')->first();
        $user = User::where('email', 'juan@example.com')->first();
        $this->assertEquals($user->id, $client->user_id);
    }

    public function test_registration_requires_rif_and_company_fiscal_name(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Juan Chirinos',
            'email' => 'juan2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rif', 'company_fiscal_name']);
    }

    public function test_registration_requires_valid_rif_format(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Juan Chirinos',
            'email' => 'juan3@example.com',
            'company_fiscal_name' => 'Empresa Test S.A.',
            'rif' => 'INVALIDO-123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rif']);
    }

    public function test_registration_rejects_duplicate_rif(): void
    {
        Client::create([
            'company_fiscal_name' => 'Empresa Existente S.A.',
            'company_short_name' => 'Existente',
            'rif' => 'J-99999999-9',
            'contact_name' => 'Representante Existente',
            'contact_email' => 'existente@empresa.com',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro Usuario',
            'email' => 'otro@example.com',
            'company_fiscal_name' => 'Otra Empresa',
            'rif' => 'J-99999999-9',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rif']);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'rif', 'company_fiscal_name']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::EMPLOYEE,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role', 'role_label'],
            ])
            ->assertJsonPath('user.role', 'employee')
            ->assertJsonPath('user.role_label', 'Empleado');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::MANAGER,
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'manager')
            ->assertJsonPath('user.role_label', 'Jefe / Gerente');
    }

    public function test_unauthenticated_user_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $this->assertCount(1, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sesión cerrada exitosamente.']);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
