<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Tabla nueva y aditiva (no existe en el legacy, no toca ninguna tabla legacy).
 * Registra acciones sensibles: anulación de recibos, cambios de usuario,
 * cargas de deuda. Definido en la Fase 8 del plan de migración, pendiente
 * hasta ahora.
 */
class Auditoria extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        // $timestamps=false (esta tabla no tiene updated_at) evita que Eloquent
        // autocastee created_at a Carbon; hay que declararlo a mano.
        return ['datos' => 'array', 'created_at' => 'datetime'];
    }

    public static function registrar(string $accion, string $descripcion, array $datos = []): void
    {
        $usuario = Auth::user();

        static::create([
            'cc_usuario' => $usuario?->cc_usuario ?? '0',
            'cc_user' => $usuario?->cc_user,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos' => $datos,
            'created_at' => now(),
        ]);
    }
}
