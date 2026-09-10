<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Registra una nueva empresa / cliente en el directorio fiscal.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $client = Client::create([
            'company_fiscal_name' => $validated['company_fiscal_name'],
            'company_short_name' => $validated['company_short_name'],
            'rif' => $validated['rif'],
            'office_phone' => $validated['office_phone'] ?? null,
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'user_id' => $validated['user_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Cliente registrado exitosamente.',
            'client' => new ClientResource($client),
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
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        $client->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente.',
            'client' => new ClientResource($client->fresh('user')),
        ]);
    }

    /**
     * Alterna el estado operativo (Activo / Inactivo) de una empresa cliente.
     */
    public function toggleStatus(Client $client): JsonResponse
    {
        $client->update([
            'is_active' => ! $client->is_active,
        ]);

        $statusMsg = $client->is_active ? 'Cliente activado exitosamente.' : 'Cliente desactivado exitosamente.';

        return response()->json([
            'message' => $statusMsg,
            'client' => new ClientResource($client->fresh('user')),
        ]);
    }

    /**
     * Elimina lógicamente (SoftDelete) un cliente del directorio fiscal.
     */
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente.',
        ]);
    }
}
