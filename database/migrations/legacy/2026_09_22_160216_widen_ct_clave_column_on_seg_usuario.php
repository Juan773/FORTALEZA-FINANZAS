<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * seg_usuario.ct_clave era varchar(45), dimensionado para hashes SHA-1 (40 chars).
 * Un hash bcrypt mide 60 chars y no entraba. Necesario para el upgrade transparente
 * de claves SHA-1 -> bcrypt en el primer login (ver App\Auth\LegacyUserProvider).
 * Explicado y aprobado con el cliente antes de aplicarse (Fase 3 del plan de migración).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->table('seg_usuario', function (Blueprint $table) {
            $table->string('ct_clave', 255)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->table('seg_usuario', function (Blueprint $table) {
            $table->string('ct_clave', 45)->nullable(false)->change();
        });
    }
};
