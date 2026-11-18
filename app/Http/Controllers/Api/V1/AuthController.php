<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Mail\ResetPasswordMail;
use App\Mail\WelcomeClientMail;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario y empresa cliente.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = UserRole::CLIENT;

        return DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'is_active' => true,
            ]);

            $companyFiscalName = $validated['company_fiscal_name'];
            $companyShortName = ! empty($validated['company_short_name'])
                ? $validated['company_short_name']
                : Str::limit($companyFiscalName, 100, '');

            $client = Client::create([
                'company_fiscal_name' => $companyFiscalName,
                'company_short_name' => $companyShortName,
                'rif' => strtoupper(trim($validated['rif'])),
                'office_phone' => $validated['phone'] ?? null,
                'contact_name' => $user->name,
                'contact_email' => $user->email,
                'contact_phone' => $validated['phone'] ?? null,
                'is_active' => true,
                'user_id' => $user->id,
            ]);

            // Enviar correo de bienvenida notificando registro de cliente
            try {
                Mail::to($user->email)->send(new WelcomeClientMail(
                    userName: $user->name,
                    userEmail: $user->email,
                    companyName: $companyFiscalName,
                    rif: strtoupper(trim($validated['rif'])),
                ));
            } catch (\Throwable $e) {
                Log::error('[AUTH_ERROR] Error al enviar correo de bienvenida al cliente: ' . $e->getMessage());
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario y empresa cliente registrados exitosamente.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
                'client' => new ClientResource($client),
            ], 201);
        });
    }

    /**
     * Inicio de sesión de usuario y emisión de token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta se encuentra desactivada. Contacta al administrador.'],
            ]);
        }

        $deviceName = $validated['device_name'] ?? 'auth_token';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Solicitud de enlace para restablecimiento de contraseña.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No encontramos ningún usuario registrado con este correo electrónico.',
                'errors' => [
                    'email' => ['No encontramos ningún usuario registrado con este correo electrónico.'],
                ],
            ], 404);
        }

        // Generación de token único seguro
        $rawToken = Str::random(64);

        // Guardar o actualizar registro en la tabla estándar password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($rawToken),
                'created_at' => Carbon::now(),
            ]
        );

        $frontendUrl = config('app.frontend_url', 'http://localhost:15001');
        $resetUrl = rtrim($frontendUrl, '/') . '/reset-password?token=' . urlencode($rawToken) . '&email=' . urlencode($user->email);

        // Enviar correo de restablecimiento con enlace
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user->name, $resetUrl, $rawToken));
        } catch (\Throwable $e) {
            Log::error('[AUTH_ERROR] Error al enviar correo de restablecimiento: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Se ha enviado un enlace de restablecimiento a tu correo electrónico.',
        ]);
    }

    /**
     * Restablece la contraseña utilizando el token y la nueva contraseña.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));

        // Buscar token en password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'message' => 'El enlace o token de restablecimiento no es válido.',
                'errors' => [
                    'token' => ['El enlace de restablecimiento es inválido o ha sido modificado.'],
                ],
            ], 422);
        }

        // Validar expiración (60 minutos)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'message' => 'El enlace de restablecimiento ha expirado. Por favor solicita uno nuevo.',
                'errors' => [
                    'token' => ['El enlace de restablecimiento ha expirado.'],
                ],
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No encontramos ningún usuario con este correo electrónico.',
            ], 404);
        }

        // Actualizar contraseña y eliminar tokens anteriores del usuario
        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();

        // Eliminar token de recuperación utilizado
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.',
        ]);
    }

    /**
     * Perfil del usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()?->load('client')),
        ]);
    }

    /**
     * Actualiza los datos de perfil, contraseña y/o empresa asociada del usuario autenticado.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Si solicita cambio de contraseña, verificar primero la contraseña actual
        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['La contraseña actual ingresada es incorrecta.'],
                ]);
            }
        }

        DB::transaction(function () use ($user, $validated) {
            $userUpdates = [];

            if (isset($validated['name'])) {
                $userUpdates['name'] = $validated['name'];
            }

            if (isset($validated['email'])) {
                $userUpdates['email'] = $validated['email'];
            }

            if (! empty($validated['password'])) {
                $userUpdates['password'] = Hash::make($validated['password']);
            }

            if (! empty($userUpdates)) {
                $user->update($userUpdates);
            }

            // Si el usuario tiene ficha de cliente asociada, sincronizar datos
            $client = $user->client;
            if ($client) {
                $clientUpdates = [];

                if (isset($validated['name'])) {
                    $clientUpdates['contact_name'] = $validated['name'];
                }

                if (isset($validated['email'])) {
                    $clientUpdates['contact_email'] = $validated['email'];
                }

                if (isset($validated['company_fiscal_name'])) {
                    $clientUpdates['company_fiscal_name'] = $validated['company_fiscal_name'];
                }

                if (isset($validated['company_short_name'])) {
                    $clientUpdates['company_short_name'] = $validated['company_short_name'];
                }

                if (isset($validated['rif'])) {
                    $clientUpdates['rif'] = $validated['rif'];
                }

                if (array_key_exists('office_phone', $validated)) {
                    $clientUpdates['office_phone'] = $validated['office_phone'];
                }

                if (array_key_exists('contact_phone', $validated)) {
                    $clientUpdates['contact_phone'] = $validated['contact_phone'];
                }

                if (! empty($clientUpdates)) {
                    $client->update($clientUpdates);
                }
            }
        });

        Log::info('[AUTH_INFO] Perfil de usuario actualizado', [
            'user_id' => $user->id,
            'role' => $user->role?->value,
            'password_changed' => ! empty($validated['password']),
        ]);

        return response()->json([
            'message' => 'Tu perfil ha sido actualizado exitosamente.',
            'user' => new UserResource($user->fresh('client')),
        ]);
    }

    /**
     * Cierre de sesión (revocación del token actual).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.',
        ]);
    }
}
