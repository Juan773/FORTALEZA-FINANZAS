<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hallazgo de la revisión de seguridad (Fase 12): el sistema legacy NUNCA
 * verifica el perfil en el servidor (s_validar_pagina() solo mira si hay
 * sesión activa) — cualquier usuario logueado podía llegar a "Usuarios" o
 * "Perfiles" escribiendo la URL a mano, sin importar su perfil. El menú solo
 * ocultaba el enlace, no protegía la pantalla.
 *
 * Esta es una restricción NUEVA, no una migración de una regla existente:
 * solo bloquea, nunca le quita acceso a quien ya lo usaba (los únicos
 * usuarios reales con perfil Administrador ya son quienes gestionan esto hoy).
 */
class EsAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (! $usuario || $usuario->perfil?->ct_perfil !== 'Administrador') {
            abort(403, 'Esta sección es solo para el perfil Administrador.');
        }

        return $next($request);
    }
}
