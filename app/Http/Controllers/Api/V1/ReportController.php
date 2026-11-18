<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GeneratorStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\GeneratorResource;
use App\Http\Resources\UserResource;
use App\Models\Client;
use App\Models\Generator;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Tablero consolidado de Reportes Ejecutivos y Supervisión Global (Etapa 5).
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $statusCounts = Generator::query()
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            $totalGenerators = array_sum($statusCounts);
            $totalKva = (float) Generator::query()->sum('capacity_kva');

            $purchaseOrder = $statusCounts[GeneratorStatus::PURCHASE_ORDER->value] ?? 0;
            $inProduction = $statusCounts[GeneratorStatus::IN_PRODUCTION->value] ?? 0;
            $originCustoms = $statusCounts[GeneratorStatus::ORIGIN_CUSTOMS->value] ?? 0;
            $maritimeTransit = $statusCounts[GeneratorStatus::MARITIME_TRANSIT->value] ?? 0;
            $destinationCustoms = $statusCounts[GeneratorStatus::DESTINATION_CUSTOMS->value] ?? 0;
            $baifaWarehouse = $statusCounts[GeneratorStatus::BAIFA_WAREHOUSE->value] ?? 0;
            $delivered = $statusCounts[GeneratorStatus::DELIVERED->value] ?? 0;

            $inProgressCount = $purchaseOrder + $inProduction + $originCustoms + $maritimeTransit + $destinationCustoms + $baifaWarehouse;

            // Resumen de Clientes
            $totalClients = Client::count();
            $activeClients = Client::where('is_active', true)->count();
            $inactiveClients = $totalClients - $activeClients;
            $clientsWithGenerators = Client::has('generators')->count();

            // Resumen de Usuarios
            $userRoleCounts = User::query()
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role')
                ->all();

            $totalUsers = array_sum($userRoleCounts);
            $activeUsers = User::where('is_active', true)->count();

            $generators = Generator::with('client')->orderBy('updated_at', 'desc')->get();
            $clients = Client::with('user')->withCount('generators')->orderBy('id', 'desc')->get();
            $users = User::with('client')->orderBy('id', 'desc')->get();

            Log::info('[REPORTES_INFO] Consulta de tablero general de reportes', [
                'user_id' => $request->user()?->id,
                'role' => $request->user()?->role?->value,
                'total_generators' => $totalGenerators,
                'total_clients' => $totalClients,
            ]);

            return response()->json([
                'generators_summary' => [
                    'total' => $totalGenerators,
                    'total_kva' => round($totalKva, 2),
                    'in_progress' => $inProgressCount,
                    'purchase_order' => $purchaseOrder,
                    'in_production' => $inProduction,
                    'origin_customs' => $originCustoms,
                    'maritime_transit' => $maritimeTransit,
                    'destination_customs' => $destinationCustoms,
                    'baifa_warehouse' => $baifaWarehouse,
                    'delivered' => $delivered,
                ],
                'clients_summary' => [
                    'total' => $totalClients,
                    'active' => $activeClients,
                    'inactive' => $inactiveClients,
                    'with_generators' => $clientsWithGenerators,
                ],
                'users_summary' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'inactive' => $totalUsers - $activeUsers,
                    'admin' => $userRoleCounts[UserRole::ADMIN->value] ?? 0,
                    'manager' => $userRoleCounts[UserRole::MANAGER->value] ?? 0,
                    'employee' => $userRoleCounts[UserRole::EMPLOYEE->value] ?? 0,
                    'client' => $userRoleCounts[UserRole::CLIENT->value] ?? 0,
                ],
                'generators' => GeneratorResource::collection($generators),
                'clients' => ClientResource::collection($clients),
                'users' => UserResource::collection($users),
            ]);
        } catch (\Throwable $e) {
            Log::error('[REPORTES_ERROR] Error al generar reporte consolidado: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al consultar los reportes del sistema.',
            ], 500);
        }
    }
}
