<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckpointController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\GeneratorController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Diagnóstico de salud del servicio
Route::get('/v1/health', function () {
    $dbStatus = 'disconnected';
    try {
        DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (Exception $e) {
        $dbStatus = 'error: '.$e->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'app' => 'baifa-api',
        'database' => $dbStatus,
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Autenticación con Laravel Sanctum (Bearer Token)
Route::prefix('v1/auth')->group(function () {
    // Rutas públicas
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Gestión de Usuarios del Sistema (Exclusivo Administrador)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
});

// Directorio Fiscal y Gestión de Clientes (Personal Interno: Admin, Manager, Empleado)
Route::middleware(['auth:sanctum', 'role:admin,manager,employee'])->prefix('v1')->group(function () {
    Route::patch('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus']);
    Route::apiResource('clients', ClientController::class);
});

// Catálogo e Inventario de Generadores Eléctricos (Etapa 2)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Métricas y consulta (disponible para todo usuario autenticado según su rol)
    Route::get('generators/summary', [GeneratorController::class, 'summary']);
    Route::get('generators', [GeneratorController::class, 'index']);
    Route::get('generators/{generator}', [GeneratorController::class, 'show']);

    // Operaciones de gestión/creación/edición (Personal Interno: Admin, Manager, Empleado)
    Route::middleware('role:admin,manager,employee')->group(function () {
        Route::post('generators', [GeneratorController::class, 'store']);
        Route::match(['put', 'patch', 'post'], 'generators/{generator}', [GeneratorController::class, 'update']);
        Route::delete('generators/{generator}', [GeneratorController::class, 'destroy']);
    });
});

// Puntos de Control y Trazabilidad Logística (Etapas 3 y 4)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Consulta rápida de trazabilidad por número de serial
    Route::get('tracking/{serial_number}', [CheckpointController::class, 'trackBySerial']);

    // Listado cronológico de puntos de control de un generador
    Route::get('generators/{generator}/checkpoints', [CheckpointController::class, 'index']);

    // Registro manual de nuevo punto de control (Personal Interno: Admin, Manager, Empleado)
    Route::middleware('role:admin,manager,employee')->group(function () {
        Route::post('generators/{generator}/checkpoints', [CheckpointController::class, 'store']);
    });
});

// Soporte Técnico y Helpdesk (Módulo de Tickets y Chat)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // 1. Operaciones del Cliente
    Route::post('support/tickets', [SupportTicketController::class, 'store']);
    Route::get('support/active-ticket', [SupportTicketController::class, 'activeTicket']);
    Route::get('support/my-tickets', [SupportTicketController::class, 'myTickets']);

    // 2. Operaciones de Tickera / Helpdesk (Personal Interno: Admin, Manager, Empleado)
    Route::middleware('role:admin,manager,employee')->group(function () {
        Route::get('support/tickets', [SupportTicketController::class, 'index']);
        Route::patch('support/tickets/{ticket}/assign', [SupportTicketController::class, 'assign']);
        Route::patch('support/tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus']);
    });

    // 3. Operaciones de visualización, chat y bitácora (Cliente sobre su ticket, o Personal Interno)
    Route::get('support/tickets/{ticket}', [SupportTicketController::class, 'show']);
    Route::get('support/tickets/{ticket}/messages', [SupportTicketController::class, 'getMessages']);
    Route::post('support/tickets/{ticket}/messages', [SupportTicketController::class, 'sendMessage']);
    Route::get('support/tickets/{ticket}/logs', [SupportTicketController::class, 'getLogs']);
});



