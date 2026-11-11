<?php

use App\Http\Middleware\EnsureLegacySessionNotExpired;
use App\Http\Middleware\EsAdministrador;
use App\Http\Middleware\PuedeEscribir;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (como Heroku/Fly) termina TLS en su borde y nos habla por HTTP
        // interno; sin esto, Laravel genera URLs http:// y el navegador bloquea
        // los assets por contenido mixto.
        $middleware->trustProxies(at: '*');

        $middleware->appendToGroup('web', EnsureLegacySessionNotExpired::class);
        $middleware->alias([
            'administrador' => EsAdministrador::class,
            'escritura' => PuedeEscribir::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
