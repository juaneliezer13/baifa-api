<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkpoint\StoreCheckpointRequest;
use App\Http\Resources\CheckpointResource;
use App\Http\Resources\GeneratorResource;
use App\Http\Resources\PublicGeneratorResource;
use App\Mail\GeneratorCheckpointMail;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckpointController extends Controller
{
    /**
     * Lista cronológica de puntos de control (checkpoints) para un generador.
     */
    public function index(Request $request, Generator $generator): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();

        // Control de acceso para usuarios con rol de cliente
        if ($user && $user->role === UserRole::CLIENT) {
            if ($generator->client_id !== $user->client?->id) {
                return response()->json([
                    'message' => 'No tiene autorización para visualizar los puntos de control de este generador.',
                ], 403);
            }
        }

        $checkpoints = $generator->checkpoints()->with('user')->get();

        return CheckpointResource::collection($checkpoints);
    }

    /**
     * Registra un nuevo punto de control (avance de ruta) y actualiza el estado del generador.
     */
    public function store(StoreCheckpointRequest $request, Generator $generator): JsonResponse
    {
        $validated = $request->validated();

        try {
            $checkpoint = DB::transaction(function () use ($generator, $validated, $request) {
                // 1. Crear el registro inmutable del checkpoint
                $newCheckpoint = $generator->checkpoints()->create([
                    'user_id' => $request->user()?->id,
                    'status' => $validated['status'],
                    'checkpoint_name' => $validated['checkpoint_name'],
                    'event_date' => $validated['event_date'] ?? now(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 2. Actualizar el estatus principal del generador
                $generator->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? $generator->notes,
                ]);

                return $newCheckpoint;
            });

            Log::info("[TRACKING_INFO] Nuevo punto de control registrado para generador {$generator->serial_number}", [
                'generator_id' => $generator->id,
                'serial_number' => $generator->serial_number,
                'status' => $validated['status'],
                'checkpoint_name' => $validated['checkpoint_name'],
                'user_id' => $request->user()?->id,
            ]);

            // Notificar por correo al cliente asignado sobre el avance / cambio del punto de control
            $client = $generator->client;
            if ($client) {
                $recipientEmail = $client->contact_email ?? $client->user?->email;
                if ($recipientEmail) {
                    try {
                        $mail = Mail::to($recipientEmail);
                        if ($client->user && $client->user->email && $client->user->email !== $recipientEmail) {
                            $mail->cc($client->user->email);
                        }
                        $mail->send(new GeneratorCheckpointMail(
                            generator: $generator,
                            checkpoint: $checkpoint,
                            client: $client,
                        ));
                    } catch (\Throwable $e) {
                        Log::error('[TRACKING_ERROR] Error al enviar notificación de punto de control por correo: '.$e->getMessage(), [
                            'generator_id' => $generator->id,
                            'checkpoint_id' => $checkpoint->id,
                            'client_id' => $client->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Punto de control registrado exitosamente.',
                'checkpoint' => new CheckpointResource($checkpoint->load('user')),
                'generator' => new GeneratorResource($generator->fresh(['client', 'checkpoints.user'])),
            ], 201);
        } catch (\Throwable $e) {
            Log::error("[TRACKING_ERROR] Fallo al registrar punto de control para generador {$generator->serial_number}", [
                'generator_id' => $generator->id,
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al procesar el punto de control.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta de trazabilidad de un generador a partir de su serial de fábrica.
     * Soporta acceso público limitado (estilo MRW) y acceso privado completo si el usuario cuenta con sesión activa y permisos.
     */
    public function trackBySerial(Request $request, string $serialNumber): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user();
        $generator = Generator::where('serial_number', $serialNumber)
            ->with(['client', 'checkpoints.user'])
            ->first();

        if (! $generator) {
            return response()->json([
                'message' => "No se encontró ningún generador registrado con el serial: {$serialNumber}",
            ], 404);
        }

        // Determinar si el usuario tiene autorización para ver los detalles privados completos
        $hasFullAccess = false;

        if ($user) {
            if (in_array($user->role, [UserRole::ADMIN, UserRole::MANAGER, UserRole::EMPLOYEE], true)) {
                $hasFullAccess = true;
            } elseif ($user->role === UserRole::CLIENT && $generator->client_id === $user->client?->id) {
                $hasFullAccess = true;
            }
        }

        if ($hasFullAccess) {
            return response()->json([
                'generator' => new GeneratorResource($generator),
            ]);
        }

        // Vista pública limitada (invitados sin sesión activa o clientes no asignados directamente)
        return response()->json([
            'generator' => new PublicGeneratorResource($generator),
        ]);
    }
}
