<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea las pantallas que solo sirven para registrar datos (Nuevo recibo,
 * Cargar deuda) a los usuarios con perfil "Solo lectura". Las acciones de los
 * demás módulos se protegen dentro de cada componente con authorize('escribir').
 */
class PuedeEscribir
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::denies('escribir')) {
            abort(403, 'Tu perfil es de solo lectura.');
        }

        return $next($request);
    }
}
