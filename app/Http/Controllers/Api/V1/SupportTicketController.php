<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\AssignTicketRequest;
use App\Http\Requests\Support\CreateTicketRequest;
use App\Http\Requests\Support\SendTicketMessageRequest;
use App\Http\Requests\Support\UpdateTicketStatusRequest;
use App\Http\Resources\SupportTicketResource;
use App\Http\Resources\TicketLogResource;
use App\Http\Resources\TicketMessageResource;
use App\Mail\TicketStatusUpdatedMail;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportTicketController extends Controller
{
    /**
     * Módulo Tickera / Helpdesk: Listado general de tickets para personal interno (Admin, Manager, Empleado).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SupportTicket::query()
            ->with(['user', 'client', 'assignedAgent'])
            ->withCount('messages');

        // Filtro por estatus
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Filtro por asignación
        if ($request->filled('assigned_to')) {
            $assigned = $request->query('assigned_to');
            if ($assigned === 'me') {
                $query->where('assigned_to_user_id', $request->user()?->id);
            } elseif ($assigned === 'unassigned') {
                $query->whereNull('assigned_to_user_id');
            } elseif (is_numeric($assigned)) {
                $query->where('assigned_to_user_id', (int) $assigned);
            }
        }

        // Búsqueda textual por código, título o cliente
        if ($request->filled('search')) {
            $term = trim((string) $request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    })
                    ->orWhereHas('client', function ($cq) use ($term) {
                        $cq->where('company_fiscal_name', 'like', "%{$term}%")
                            ->orWhere('rif', 'like', "%{$term}%");
                    });
            });
        }

        // Ordenamiento por fecha de actualización descendente
        $query->orderBy('updated_at', 'desc');

        $perPage = (int) $request->query('per_page', 15);
        $tickets = $query->paginate($perPage);

        return SupportTicketResource::collection($tickets);
    }

    /**
     * Módulo del Cliente: "Mis Consultas" categorizadas y filtrables.
     */
    public function myTickets(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = SupportTicket::query()
            ->where('user_id', $user->id)
            ->with(['assignedAgent'])
            ->withCount('messages');

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Filtro por estatus
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Búsqueda textual
        if ($request->filled('search')) {
            $term = trim((string) $request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = (int) $request->query('per_page', 10);
        $tickets = $query->paginate($perPage);

        return SupportTicketResource::collection($tickets);
    }

    /**
     * Obtiene el ticket activo en curso para el cliente autenticado (para desplegar el chat flotante).
     * Si no tiene ningún ticket activo ('pending' o 'in_progress'), retorna data: null.
     */
    public function activeTicket(Request $request): JsonResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [TicketStatus::PENDING, TicketStatus::IN_PROGRESS])
            ->with(['assignedAgent', 'messages.user'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $ticket) {
            return response()->json([
                'data' => null,
                'has_active' => false,
            ]);
        }

        return response()->json([
            'data' => (new SupportTicketResource($ticket))->resolve(),
            'has_active' => true,
        ]);
    }

    /**
     * Registro y creación de un nuevo ticket de soporte por parte del cliente.
     */
    public function store(CreateTicketRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $ticket = DB::transaction(function () use ($validated, $user) {
                $code = SupportTicket::generateCode();

                $newTicket = SupportTicket::create([
                    'code' => $code,
                    'user_id' => $user->id,
                    'client_id' => $user->client?->id,
                    'title' => $validated['title'],
                    'category' => $validated['category'] ?? TicketCategory::TECHNICAL_SUPPORT->value,
                    'description' => $validated['description'] ?? null,
                    'status' => TicketStatus::PENDING,
                ]);

                // Registro inicial en bitácora
                $newTicket->logs()->create([
                    'user_id' => $user->id,
                    'action' => 'ticket_created',
                    'previous_status' => null,
                    'new_status' => TicketStatus::PENDING->value,
                    'comment' => 'Ticket de soporte generado por el cliente.',
                ]);

                // Mensaje inicial del sistema en el chat
                $newTicket->messages()->create([
                    'user_id' => null,
                    'sender_type' => 'system',
                    'message' => "Ticket #{$code} creado con éxito: \"{$newTicket->title}\". En cola del Helpdesk a la espera de asignación de operador.",
                ]);

                // Si el cliente adjuntó descripción, se registra como primer mensaje del cliente
                if (! empty($validated['description'])) {
                    $newTicket->messages()->create([
                        'user_id' => $user->id,
                        'sender_type' => 'client',
                        'message' => $validated['description'],
                    ]);
                }

                return $newTicket;
            });

            Log::info("[TICKETS_INFO] Ticket #{$ticket->code} generado por el usuario #{$user->id} ({$user->name}).");

            $ticket->load(['assignedAgent', 'messages.user', 'logs.user']);

            return (new SupportTicketResource($ticket))
                ->response()
                ->setStatusCode(201);
        } catch (\Throwable $e) {
            Log::error("[TICKETS_ERROR] Error al crear ticket: {$e->getMessage()}");

            return response()->json([
                'message' => 'Ocurrió un error interno al registrar el ticket de soporte.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra el detalle completo de un ticket con sus mensajes y bitácora.
     */
    public function show(Request $request, SupportTicket $ticket): JsonResponse|SupportTicketResource
    {
        $user = $request->user();

        // Si es cliente, solo puede ver sus propios tickets
        if ($user->role === UserRole::CLIENT && $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene autorización para visualizar este ticket de soporte.',
            ], 403);
        }

        $ticket->load(['user', 'client', 'assignedAgent', 'messages.user', 'logs.user']);

        return new SupportTicketResource($ticket);
    }

    /**
     * Asignación de ticket: Un administrador puede asignar a cualquier operador,
     * o un empleado/admin puede "tomar" el ticket directamente.
     */
    public function assign(AssignTicketRequest $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Determinar a quién se asigna
        $assignedUserId = $validated['assigned_to_user_id'] ?? $user->id;
        $assignedUser = User::find($assignedUserId);

        if (! $assignedUser) {
            return response()->json([
                'message' => 'El operador especificado no existe.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($ticket, $assignedUser, $user, $validated) {
                $oldStatus = $ticket->status;
                $newStatus = TicketStatus::IN_PROGRESS;

                $ticket->assigned_to_user_id = $assignedUser->id;
                $ticket->status = $newStatus;
                $ticket->save();

                $isSelfAssign = ($assignedUser->id === $user->id);
                $actionName = $isSelfAssign ? 'ticket_self_assigned' : 'ticket_assigned';
                $logComment = $isSelfAssign
                    ? "El operador {$assignedUser->name} tomó el ticket de la tickera."
                    : "El administrador {$user->name} asignó el ticket al operador {$assignedUser->name}.";

                if (! empty($validated['comment'])) {
                    $logComment .= " Nota: {$validated['comment']}";
                }

                // Bitácora
                $ticket->logs()->create([
                    'user_id' => $user->id,
                    'action' => $actionName,
                    'previous_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'comment' => $logComment,
                ]);

                // Mensaje en el chat
                $ticket->messages()->create([
                    'user_id' => null,
                    'sender_type' => 'system',
                    'message' => "{$assignedUser->name} ({$assignedUser->role->label()}) ha sido asignado a la atención de este ticket. El chat en vivo está ahora abierto.",
                ]);
            });

            Log::info("[TICKETS_INFO] Ticket #{$ticket->code} asignado a {$assignedUser->name} por {$user->name}.");

            // Envío de correos de notificación de cambio de estatus a En Proceso
            $this->notifyStatusChanged($ticket, TicketStatus::PENDING, TicketStatus::IN_PROGRESS, "Operador {$assignedUser->name} asignado", $user->name);

            $ticket->load(['user', 'client', 'assignedAgent', 'messages.user', 'logs.user']);

            return (new SupportTicketResource($ticket))
                ->response()
                ->setStatusCode(200);
        } catch (\Throwable $e) {
            Log::error("[TICKETS_ERROR] Error al asignar ticket #{$ticket->code}: {$e->getMessage()}");

            return response()->json([
                'message' => 'Ocurrió un error al procesar la asignación del ticket.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza el estatus del ticket (En Proceso, Finalizado, Cancelado).
     * Exclusivo para Administradores y Empleados (el cliente no puede cambiar estados).
     */
    public function updateStatus(UpdateTicketStatusRequest $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $oldStatus = $ticket->status;
        $newStatus = TicketStatus::from($validated['status']);

        try {
            DB::transaction(function () use ($ticket, $oldStatus, $newStatus, $validated, $user) {
                $ticket->status = $newStatus;

                if (in_array($newStatus, [TicketStatus::FINISHED, TicketStatus::CANCELLED], true)) {
                    $ticket->final_comment = $validated['final_comment'] ?? null;
                    $ticket->closed_at = now();
                }

                $ticket->save();

                $action = match ($newStatus) {
                    TicketStatus::FINISHED => 'ticket_finished',
                    TicketStatus::CANCELLED => 'ticket_cancelled',
                    default => 'status_changed',
                };

                $comment = $validated['final_comment'] ?? $validated['comment'] ?? "Estatus actualizado a {$newStatus->label()}.";

                // Registro en bitácora
                $ticket->logs()->create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'previous_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'comment' => $comment,
                ]);

                // Mensaje del sistema en el chat
                $ticket->messages()->create([
                    'user_id' => null,
                    'sender_type' => 'system',
                    'message' => "Estatus del ticket actualizado a: {$newStatus->label()} por {$user->name}. " . ($ticket->final_comment ? "Comentario: \"{$ticket->final_comment}\"" : ''),
                ]);
            });

            Log::info("[TICKETS_INFO] Ticket #{$ticket->code} cambió estatus de {$oldStatus->value} a {$newStatus->value} por {$user->name}.");

            // Notificación por correo al cliente y al empleado asignado
            $comment = $validated['final_comment'] ?? $validated['comment'] ?? null;
            $this->notifyStatusChanged($ticket, $oldStatus, $newStatus, $comment, $user->name);

            $ticket->load(['user', 'client', 'assignedAgent', 'messages.user', 'logs.user']);

            return (new SupportTicketResource($ticket))
                ->response()
                ->setStatusCode(200);
        } catch (\Throwable $e) {
            Log::error("[TICKETS_ERROR] Error al cambiar estatus de ticket #{$ticket->code}: {$e->getMessage()}");

            return response()->json([
                'message' => 'Ocurrió un error al actualizar el estatus del ticket.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los mensajes del chat del ticket.
     */
    public function getMessages(Request $request, SupportTicket $ticket): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::CLIENT && $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene autorización para visualizar los mensajes de este ticket.',
            ], 403);
        }

        $messages = $ticket->messages()->with('user')->get();

        return TicketMessageResource::collection($messages);
    }

    /**
     * Envía un mensaje al chat del ticket.
     */
    public function sendMessage(SendTicketMessageRequest $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->role === UserRole::CLIENT && $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene autorización para enviar mensajes a este ticket.',
            ], 403);
        }

        // Si el ticket ya está finalizado o cancelado, no permitir más mensajes
        if (in_array($ticket->status, [TicketStatus::FINISHED, TicketStatus::CANCELLED], true)) {
            return response()->json([
                'message' => 'No es posible enviar mensajes en un ticket cerrado o cancelado.',
            ], 422);
        }

        $senderType = ($user->role === UserRole::CLIENT) ? 'client' : 'agent';

        try {
            $message = $ticket->messages()->create([
                'user_id' => $user->id,
                'sender_type' => $senderType,
                'message' => $validated['message'],
            ]);

            $message->load('user');

            // Actualizar timestamp del ticket para que se mueva a la cima en la lista
            $ticket->touch();

            return (new TicketMessageResource($message))
                ->response()
                ->setStatusCode(201);
        } catch (\Throwable $e) {
            Log::error("[TICKETS_ERROR] Error al enviar mensaje en ticket #{$ticket->code}: {$e->getMessage()}");

            return response()->json([
                'message' => 'Ocurrió un error al enviar el mensaje.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la bitácora histórica y de evolución del ticket.
     */
    public function getLogs(Request $request, SupportTicket $ticket): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::CLIENT && $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene autorización para ver la bitácora de este ticket.',
            ], 403);
        }

        $logs = $ticket->logs()->with('user')->get();

        return TicketLogResource::collection($logs);
    }

    /**
     * Helper para disparar las notificaciones por correo electrónico ante cambios de estatus.
     */
    protected function notifyStatusChanged(
        SupportTicket $ticket,
        TicketStatus $oldStatus,
        TicketStatus $newStatus,
        ?string $comment,
        ?string $changedByName
    ): void {
        try {
            $oldStatusLabel = $oldStatus->label();
            $newStatusLabel = $newStatus->label();

            // 1. Notificación al cliente
            if ($ticket->user?->email) {
                Mail::to($ticket->user->email)->send(new TicketStatusUpdatedMail(
                    ticket: $ticket,
                    recipientRole: 'client',
                    oldStatusLabel: $oldStatusLabel,
                    newStatusLabel: $newStatusLabel,
                    comment: $comment,
                    changedByName: $changedByName
                ));
            }

            // 2. Notificación al operador asignado
            if ($ticket->assignedAgent?->email) {
                Mail::to($ticket->assignedAgent->email)->send(new TicketStatusUpdatedMail(
                    ticket: $ticket,
                    recipientRole: 'employee',
                    oldStatusLabel: $oldStatusLabel,
                    newStatusLabel: $newStatusLabel,
                    comment: $comment,
                    changedByName: $changedByName
                ));
            }
        } catch (\Throwable $e) {
            Log::error("[TICKETS_MAIL_ERROR] No se pudo despachar el correo de estatus para ticket #{$ticket->code}: {$e->getMessage()}");
        }
    }
}
