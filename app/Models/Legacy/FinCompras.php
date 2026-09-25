<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Egreso/gasto a proveedor. emp_ruc/emp_razon_social están denormalizados
 * a propósito (histórico inmune a cambios posteriores del proveedor).
 *
 * OJO: a diferencia de todo el resto del sistema (borrado lógico universal
 * vía ct_vigencia), dao_fin_compras::eliminar() legacy hace un DELETE físico
 * real. Se documenta la inconsistencia en Index::eliminar().
 */
class FinCompras extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_compras';
    protected $primaryKey = 'cc_compras';
    public $timestamps = false;

    protected $guarded = [];
}
