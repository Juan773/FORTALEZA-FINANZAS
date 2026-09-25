<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replica util/seguridad.php::s_validar_pagina(): cada usuario tiene su propio
 * tiempo de sesión (seg_usuario.nn_tiempo_sesion, en minutos) en vez de un único
 * SESSION_LIFETIME global. Si pasó más tiempo que eso desde la última petición,
 * se cierra la sesión.
 */
class EnsureLegacySessionNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if ($usuario) {
            $ultimoAcceso = $request->session()->get('ultimo_acceso');
            $limiteMinutos = (int) $usuario->nn_tiempo_sesion;

            if ($ultimoAcceso && $limiteMinutos > 0 && now()->diffInMinutes($ultimoAcceso) >= $limiteMinutos) {
                Auth::logout();
                $request->session()->invalidate();

                return redirect('/login')->with('status', 'Sesión expirada por inactividad.');
            }

            $request->session()->put('ultimo_acceso', now());
        }

        return $next($request);
    }
}
