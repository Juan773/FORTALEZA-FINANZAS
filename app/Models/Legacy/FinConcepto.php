<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Concepto de ingreso/egreso (plan de cuentas simplificado). PK autoincremental,
 * a diferencia de gen_personas/seg_usuario que usan el patrón MAX+1 legacy.
 */
class FinConcepto extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_concepto';
    protected $primaryKey = 'cc_concepto';
    public $timestamps = false;

    protected $guarded = [];

    public function subconceptos(): HasMany
    {
        return $this->hasMany(FinSubconcepto::class, 'cc_concepto', 'cc_concepto');
    }
}
