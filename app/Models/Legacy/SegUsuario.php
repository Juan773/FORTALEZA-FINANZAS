<?php

namespace App\Models\Legacy;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\DB;

/**
 * Usuario del sistema, leído desde la vista legacy `vt_empleado_usuario`
 * (join de gen_personas + seg_usuario, ver bo_seg_usuario::validar()).
 * cc_usuario === gen_personas.cc_persona: todo usuario del sistema es también un socio.
 *
 * Es de solo lectura para el login (por eso mapea a la vista, no a la tabla).
 * Para escribir (rehash de clave) usa updateClaveHash(), que sí actualiza la
 * tabla base `seg_usuario`, porque no todas las vistas son escribibles.
 */
class SegUsuario extends Model implements Authenticatable
{
    use Authorizable;

    protected $connection = 'legacy';
    protected $table = 'vt_empleado_usuario';
    protected $primaryKey = 'cc_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
    protected $hidden = ['ct_clave'];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(GenPersona::class, 'cc_usuario', 'cc_persona');
    }

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(SegPerfil::class, 'cc_perfil', 'cc_perfil');
    }

    /**
     * Acceso habilitado y socio vigente (mismas dos condiciones que bo_seg_usuario::validar()).
     * cfl_acceso/cfl_vigencia son `int unsigned` (no texto): comparar con '1' estricto
     * siempre da false. Se usa == (no ===) a propósito para tolerar int o string.
     */
    public function puedeIngresar(): bool
    {
        return $this->cfl_acceso == '1' && $this->cfl_vigencia == '1';
    }

    /** Reemplaza el hash legacy (SHA-1) por uno bcrypt/argon2, en la tabla base. */
    public function actualizarHashClave(string $nuevoHash): void
    {
        DB::connection('legacy')
            ->table('seg_usuario')
            ->where('cc_usuario', $this->cc_usuario)
            ->update(['ct_clave' => $nuevoHash]);
    }

    // --- Contrato Authenticatable ---

    public function getAuthIdentifierName(): string
    {
        return 'cc_usuario';
    }

    public function getAuthIdentifier()
    {
        return $this->cc_usuario;
    }

    public function getAuthPasswordName(): string
    {
        return 'ct_clave';
    }

    public function getAuthPassword(): string
    {
        return $this->ct_clave;
    }

    public function getRememberToken()
    {
        return null; // legacy no soporta "recordarme"; se agrega si el negocio lo pide.
    }

    public function setRememberToken($value): void
    {
        // no-op a propósito
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
