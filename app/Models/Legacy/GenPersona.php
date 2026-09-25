<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Socio/asociado. Mapea 1:1 la tabla legacy `gen_personas` (sin migraciones nuevas todavía).
 */
class GenPersona extends Model
{
    protected $connection = 'legacy';
    protected $table = 'gen_personas';
    protected $primaryKey = 'cc_persona';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    /**
     * cc_persona es `int(6) zerofill`: PDO lo devuelve como string con ceros
     * a la izquierda ("000001"), mientras que las tablas hijas (fin_caja.cc_persona,
     * fin_estado_cuenta.cc_persona) son `int` normal y llegan como entero PHP (1).
     * Sin este cast explícito, las relaciones belongsTo/hasMany nunca encuentran
     * coincidencia ("1" !== "000001" como valores de array) y las relaciones
     * quedan silenciosamente en null. Ver hallazgo documentado en la sesión de
     * migración (recibo sin nombre de socio en el listado de Caja).
     * Para mostrar el código con ceros a la izquierda, usar codigoFormateado().
     */
    protected function casts(): array
    {
        return ['cc_persona' => 'integer'];
    }

    public function codigoFormateado(): string
    {
        return str_pad((string) $this->cc_persona, 6, '0', STR_PAD_LEFT);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(FinEstadoCuenta::class, 'cc_persona', 'cc_persona');
    }

    /**
     * cc_persona no es AUTO_INCREMENT (la BD legacy usa fc_correlativo = MAX+1).
     * Se calcula bajo lock de fila para evitar que dos altas simultáneas choquen,
     * algo que la función legacy original no garantizaba.
     */
    public static function siguienteId(): string
    {
        $max = DB::connection('legacy')->table('gen_personas')->lockForUpdate()->max('cc_persona');

        return str_pad((string) ((int) $max + 1), 6, '0', STR_PAD_LEFT);
    }
}
