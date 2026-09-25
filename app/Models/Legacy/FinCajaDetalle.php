<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinCajaDetalle extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_caja_detalle';
    protected $primaryKey = 'cc_caja_det';
    public $timestamps = false;

    protected $guarded = [];

    /** cc_concepto es int(4) zerofill; ver GenPersona/FinSubconcepto por qué hace falta. */
    protected function casts(): array
    {
        return ['cc_concepto' => 'integer'];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(FinCaja::class, 'cc_caja', 'cc_caja');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(FinConcepto::class, 'cc_concepto', 'cc_concepto');
    }
}
