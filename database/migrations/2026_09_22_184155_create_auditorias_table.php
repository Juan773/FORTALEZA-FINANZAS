<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->string('cc_usuario', 10);
            $table->string('cc_user', 45)->nullable();
            $table->string('accion');
            $table->string('descripcion');
            $table->json('datos')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['accion', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
