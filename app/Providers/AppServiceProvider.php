<?php

namespace App\Providers;

use App\Auth\LegacyUserProvider;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('legacy', fn () => new LegacyUserProvider());

        // Migraciones que alteran el esquema legacy (siempre aditivas/explicadas primero,
        // nunca DROP), separadas de las migraciones propias de la app en database/migrations.
        $this->loadMigrationsFrom(database_path('migrations/legacy'));
    }
}
