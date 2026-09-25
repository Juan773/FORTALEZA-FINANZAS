<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Proveedor. emp_ruc ahora es varchar(11) (ver migración widen_emp_ruc_column_on_gen_empresa);
 * antes era INT y truncaba RUCs reales por overflow.
 */
class GenEmpresa extends Model
{
    protected $connection = 'legacy';
    protected $table = 'gen_empresa';
    protected $primaryKey = 'emp_id';
    public $timestamps = false;

    protected $guarded = [];
}
