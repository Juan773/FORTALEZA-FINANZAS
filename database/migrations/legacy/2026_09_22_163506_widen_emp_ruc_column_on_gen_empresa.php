<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * gen_empresa.emp_ruc era INT (máx. ~2,147 millones); un RUC peruano tiene 11 dígitos.
 * Encontramos un proveedor real con emp_ruc=2147483647 (el tope exacto de un INT con
 * signo): su RUC verdadero ya se truncó en el dump original y esta migración no lo
 * recupera, solo evita que vuelva a pasar. Aprobado con el cliente antes de aplicarse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->table('gen_empresa', function (Blueprint $table) {
            $table->string('emp_ruc', 11)->change();
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->table('gen_empresa', function (Blueprint $table) {
            $table->integer('emp_ruc')->change();
        });
    }
};
