<?php

namespace App\Providers;

use App\Auth\LegacyUserProvider;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('legacy', fn () => new LegacyUserProvider);

        // Todo lo que crea, edita o anula datos pasa por este Gate (acciones Livewire,
        // middleware 'escritura' y los botones en las vistas con @can('escribir')).
        Gate::define('escribir', fn (SegUsuario $usuario) => ! $usuario->esSoloLectura());

        // Migraciones que alteran el esquema legacy (siempre aditivas/explicadas primero,
        // nunca DROP), separadas de las migraciones propias de la app en database/migrations.
        $this->loadMigrationsFrom(database_path('migrations/legacy'));
    }
}
