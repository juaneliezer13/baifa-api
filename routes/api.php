<?php

use App\Http\Controllers\Api\V1\AuthController;
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

    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
