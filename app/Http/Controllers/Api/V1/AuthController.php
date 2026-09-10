<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Mail\ResetPasswordMail;
use App\Mail\WelcomeClientMail;
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
     * Registro de nuevo usuario.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = isset($validated['role'])
            ? UserRole::from($validated['role'])
            : UserRole::CLIENT;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        // Enviar correo simple de bienvenida notificando registro de cliente
        try {
            Mail::to($user->email)->send(new WelcomeClientMail($user->name, $user->email));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de bienvenida: ' . $e->getMessage());
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
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

        // Validación de cuentas institucionales de prueba protegidas
        $blockedEmails = ['admin@baifa.com.ve', 'cliente.real@empresa.com'];
        if (in_array($email, $blockedEmails, true)) {
            return response()->json([
                'message' => 'Por motivos de seguridad, el restablecimiento de contraseña no está disponible para las cuentas institucionales de prueba.',
                'errors' => [
                    'email' => ['Por motivos de seguridad, el restablecimiento de contraseña no está disponible para las cuentas institucionales de prueba.'],
                ],
            ], 422);
        }

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

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $resetUrl = rtrim($frontendUrl, '/') . '/reset-password?token=' . urlencode($rawToken) . '&email=' . urlencode($user->email);

        // Enviar correo de restablecimiento con enlace
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user->name, $resetUrl, $rawToken));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de restablecimiento: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Se ha enviado un enlace de restablecimiento a tu correo electrónico.',
            'reset_url' => $resetUrl,
            'token' => $rawToken,
        ]);
    }

    /**
     * Restablece la contraseña utilizando el token y la nueva contraseña.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));

        // Validación de cuentas institucionales de prueba protegidas
        $blockedEmails = ['admin@baifa.com.ve', 'cliente.real@empresa.com'];
        if (in_array($email, $blockedEmails, true)) {
            return response()->json([
                'message' => 'Por motivos de seguridad, el restablecimiento de contraseña no está disponible para las cuentas institucionales de prueba.',
                'errors' => [
                    'email' => ['Por motivos de seguridad, el restablecimiento de contraseña no está disponible para las cuentas institucionales de prueba.'],
                ],
            ], 422);
        }

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
            'user' => new UserResource($request->user()),
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
