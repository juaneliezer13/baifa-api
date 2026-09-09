<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja la petición entrante verificando si el usuario posee alguno de los roles requeridos.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->value, $roles, true)) {
            return response()->json([
                'message' => 'No tienes permisos suficientes para acceder a este recurso.',
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
