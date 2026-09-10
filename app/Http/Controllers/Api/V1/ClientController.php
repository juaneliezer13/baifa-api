<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Mail\WelcomeUserCreatedMail;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    /**
     * Listado del directorio fiscal de clientes con filtros de búsqueda y estado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query()->with('user');

        // Búsqueda textual por Razón Social, Nombre Corto, RIF o Contacto
        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        // Filtro por estado activo / inactivo
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = in_array($request->status, ['active', '1', 'true', true], true);
            $query->where('is_active', $isActive);
        }

        // Ordenamiento por ID o nombre
        $clients = $query->orderBy('id', 'asc')->get();

        return ClientResource::collection($clients);
    }

    /**
     * Registra una nueva empresa / cliente en el directorio fiscal y crea su cuenta de usuario con rol cliente.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $client = DB::transaction(function () use ($validated) {
            $isActive = $validated['is_active'] ?? true;

            // 1. Crear usuario con rol de cliente a partir de los datos de contacto
            $user = User::create([
                'name' => $validated['contact_name'],
                'email' => $validated['contact_email'],
                'password' => Hash::make('12345678'),
                'role' => UserRole::CLIENT,
                'is_active' => $isActive,
            ]);

            // 2. Crear ficha de cliente vinculada a la cuenta de usuario
            $client = Client::create([
                'company_fiscal_name' => $validated['company_fiscal_name'],
                'company_short_name' => $validated['company_short_name'],
                'rif' => $validated['rif'],
                'office_phone' => $validated['office_phone'] ?? null,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'] ?? null,
                'is_active' => $isActive,
                'user_id' => $user->id,
            ]);

            return $client;
        });

        // 3. Enviar correo de notificación con credenciales de acceso iniciales
        try {
            Mail::to($client->contact_email)->send(new WelcomeUserCreatedMail(
                userName: $client->contact_name,
                userEmail: $client->contact_email,
                roleName: 'Cliente',
                initialPassword: '12345678'
            ));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de bienvenida al cliente: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Cliente y usuario de acceso creados exitosamente.',
            'client' => new ClientResource($client->load('user')),
        ], 201);
    }

    /**
     * Muestra la ficha técnica detallada de un cliente.
     */
    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'client' => new ClientResource($client->load('user')),
        ]);
    }

    /**
     * Actualiza los datos fiscales o de contacto de un cliente existente.
     * Sincroniza automáticamente el correo del usuario cliente asociado si se modifica.
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($client, $validated) {
            $client->update($validated);

            // Sincronizar datos con la cuenta de usuario vinculada
            if ($client->user) {
                $userUpdates = [];

                if (isset($validated['contact_email']) && $client->user->email !== $validated['contact_email']) {
                    $userUpdates['email'] = $validated['contact_email'];
                }

                if (isset($validated['is_active'])) {
                    $userUpdates['is_active'] = (bool) $validated['is_active'];
                }

                if (! empty($userUpdates)) {
                    $client->user->update($userUpdates);
                }
            }
        });

        return response()->json([
            'message' => 'Cliente actualizado exitosamente.',
            'client' => new ClientResource($client->fresh('user')),
        ]);
    }

    /**
     * Alterna el estado operativo (Activo / Inactivo) de una empresa cliente y su usuario asociado.
     */
    public function toggleStatus(Client $client): JsonResponse
    {
        $newStatus = ! $client->is_active;
        $client->update([
            'is_active' => $newStatus,
        ]);

        if ($client->user) {
            $client->user->update([
                'is_active' => $newStatus,
            ]);
        }

        $statusMsg = $client->is_active ? 'Cliente activado exitosamente.' : 'Cliente desactivado exitosamente.';

        return response()->json([
            'message' => $statusMsg,
            'client' => new ClientResource($client->fresh('user')),
        ]);
    }

    /**
     * Elimina lógicamente (SoftDelete) un cliente del directorio fiscal y elimina su usuario asociado.
     * Revoca los tokens de acceso y elimina al usuario de la tabla users.
     */
    public function destroy(Client $client): JsonResponse
    {
        DB::transaction(function () use ($client) {
            $user = $client->user;

            $client->delete();

            if ($user) {
                $user->tokens()->delete();
                $user->delete();
            }
        });

        return response()->json([
            'message' => 'Cliente y su usuario asociado eliminados exitosamente.',
        ]);
    }
}
