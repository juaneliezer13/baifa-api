<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\WelcomeUserCreatedMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Listado de usuarios del sistema con filtros de búsqueda, rol y estado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query();

        // Búsqueda por nombre o correo
        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filtro por estado activo / inactivo
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = in_array($request->status, ['active', '1', 'true', true], true);
            $query->where('is_active', $isActive);
        }

        // Ordenamiento por ID o fecha
        $users = $query->orderBy('id', 'asc')->get();

        return UserResource::collection($users);
    }

    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Enviar correo de notificación al nuevo usuario con su contraseña inicial
        try {
            $roleValue = $user->role->value ?? (string) $user->role;
            $roleLabel = match ($roleValue) {
                'admin' => 'Administrador',
                'operator' => 'Operador',
                'auditor' => 'Auditor',
                'client' => 'Cliente',
                'employee' => 'Empleado',
                default => ucfirst($roleValue),
            };

            Mail::to($user->email)->send(new WelcomeUserCreatedMail(
                userName: $user->name,
                userEmail: $user->email,
                roleName: $roleLabel,
                initialPassword: $validated['password']
            ));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de bienvenida de usuario creado: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Muestra el detalle de un usuario específico.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Actualiza los datos de un usuario existente.
     * Restricción: No se permite auto-modificación desde la administración general.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes modificar tu propio usuario desde la gestión de usuarios. Para modificar tus datos utiliza la opción Mi Perfil.',
            ], 403);
        }

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Si el usuario es de tipo cliente y se modificó su correo, sincronizarlo con su ficha de cliente
        if ($user->isClient() && isset($validated['email'])) {
            $client = $user->client ?? \App\Models\Client::where('user_id', $user->id)->first();
            if ($client && $client->contact_email !== $validated['email']) {
                $client->update(['contact_email' => $validated['email']]);
            }
        }

        return response()->json([
            'message' => 'Usuario actualizado exitosamente.',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Elimina un usuario del sistema.
     * Restricción: No se permite auto-eliminación de la cuenta propia.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta de usuario administrador.',
            ], 403);
        }

        // Revocar todos los tokens de acceso del usuario antes de eliminar
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente.',
        ]);
    }
}
