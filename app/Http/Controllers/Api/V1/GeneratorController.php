<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GeneratorStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Generator\StoreGeneratorRequest;
use App\Http\Requests\Generator\UpdateGeneratorRequest;
use App\Http\Resources\GeneratorResource;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeneratorController extends Controller
{
    /**
     * Listado de generadores eléctricos con filtros por búsqueda, estado y cliente.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Generator::query()->with('client');

        // Si es rol cliente, restringir estrictamente a los generadores asociados a su empresa
        if ($user && $user->role === UserRole::CLIENT) {
            $client = $user->client;
            if ($client) {
                $query->where('client_id', $client->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        // Búsqueda textual por serial, modelo, nombre o razón social del cliente
        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        // Filtro por estado
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->string('status'));
        }

        $generators = $query->orderBy('id', 'desc')->get();

        return GeneratorResource::collection($generators);
    }

    /**
     * Resumen de métricas y contadores de inventario por estado.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $baseQuery = Generator::query();

        if ($user && $user->role === UserRole::CLIENT) {
            $client = $user->client;
            if ($client) {
                $baseQuery->where('client_id', $client->id);
            } else {
                $baseQuery->whereRaw('1 = 0');
            }
        }

        $counts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $total = array_sum($counts);

        return response()->json([
            'total' => $total,
            'warehouse' => $counts[GeneratorStatus::WAREHOUSE->value] ?? 0,
            'in_transit' => $counts[GeneratorStatus::IN_TRANSIT->value] ?? 0,
            'checkpoint' => $counts[GeneratorStatus::CHECKPOINT->value] ?? 0,
            'delivered' => $counts[GeneratorStatus::DELIVERED->value] ?? 0,
            'installed' => $counts[GeneratorStatus::INSTALLED->value] ?? 0,
        ]);
    }

    /**
     * Registra un nuevo generador eléctrico en el inventario.
     */
    public function store(StoreGeneratorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Manejo de carga de imagen si se envió archivo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('generators', 'public');
            $validated['photo_path'] = $photoPath;
        }

        unset($validated['photo']);

        $generator = Generator::create($validated);

        Log::info('[GENERADORES_INFO] Nuevo generador registrado', [
            'id' => $generator->id,
            'serial' => $generator->serial_number,
            'model' => $generator->model,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Generador registrado exitosamente en el catálogo.',
            'generator' => new GeneratorResource($generator->load('client')),
        ], 201);
    }

    /**
     * Muestra la ficha detallada de un generador.
     */
    public function show(Request $request, Generator $generator): JsonResponse
    {
        $user = $request->user();

        // Control de acceso para cliente
        if ($user && $user->role === UserRole::CLIENT) {
            if ($generator->client_id !== $user->client?->id) {
                return response()->json([
                    'message' => 'No tiene autorización para visualizar este generador.',
                ], 403);
            }
        }

        return response()->json([
            'generator' => new GeneratorResource($generator->load('client')),
        ]);
    }

    /**
     * Actualiza la información técnica o asignación de un generador.
     */
    public function update(UpdateGeneratorRequest $request, Generator $generator): JsonResponse
    {
        $validated = $request->validated();

        // Manejo de actualización de foto
        if ($request->hasFile('photo')) {
            // Eliminar imagen anterior si está en storage local
            if ($generator->photo_path && Storage::disk('public')->exists($generator->photo_path)) {
                Storage::disk('public')->delete($generator->photo_path);
            }

            $validated['photo_path'] = $request->file('photo')->store('generators', 'public');
        }

        unset($validated['photo']);

        $generator->update($validated);

        Log::info('[GENERADORES_INFO] Generador actualizado', [
            'id' => $generator->id,
            'serial' => $generator->serial_number,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Ficha del generador actualizada exitosamente.',
            'generator' => new GeneratorResource($generator->fresh('client')),
        ]);
    }

    /**
     * Elimina lógicamente un generador del catálogo.
     */
    public function destroy(Request $request, Generator $generator): JsonResponse
    {
        $serial = $generator->serial_number;
        $id = $generator->id;

        $generator->delete();

        Log::info('[GENERADORES_INFO] Generador eliminado del catálogo', [
            'id' => $id,
            'serial' => $serial,
            'deleted_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => "Generador {$serial} eliminado del catálogo exitosamente.",
        ]);
    }
}
