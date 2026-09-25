<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Perfil/rol legacy (Administrador, Asistente, Visita, Cajero).
 * `cc_modulo_defecto` (módulo por defecto tras el login) no se migra: el sistema
 * nuevo usa un menú fijo, no el menú dinámico basado en seg_modulo del legacy.
 */
class SegPerfil extends Model
{
    protected $connection = 'legacy';
    protected $table = 'seg_perfil';
    protected $primaryKey = 'cc_perfil';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    /** cc_perfil no es AUTO_INCREMENT (dao_seg_perfil usa fc_correlativo = MAX+1). */
    public static function siguienteId(): int
    {
        return (int) DB::connection('legacy')->table('seg_perfil')->lockForUpdate()->max('cc_perfil') + 1;
    }
}
