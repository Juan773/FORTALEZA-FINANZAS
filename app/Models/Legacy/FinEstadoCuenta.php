<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Libro mayor por socio. ct_tipo: 'D' = cargo/deuda (ct_monto positivo), 'P' = pago/abono (ct_monto negativo).
 * El saldo del socio es la suma algebraica de ct_monto (ver fn_deuda en la BD legacy).
 */
class FinEstadoCuenta extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_estado_cuenta';
    protected $primaryKey = 'cc_estadocuenta';
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ct_monto' => 'decimal:2',
            'ct_fecha' => 'datetime',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(GenPersona::class, 'cc_persona', 'cc_persona');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(FinCaja::class, 'cc_caja', 'cc_caja');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(FinConcepto::class, 'cc_concepto', 'cc_concepto');
    }

    public function scopeVigente($query)
    {
        return $query->where('ct_vigencia', '1');
    }
}
