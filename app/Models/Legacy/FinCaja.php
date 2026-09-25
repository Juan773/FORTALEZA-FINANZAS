<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cabecera del recibo de caja. cc_caja SÍ es autoincremental en el esquema real
 * (verificado con SHOW CREATE TABLE); el PHP legacy simplemente nunca lo usaba,
 * generaba su propio ID vía una función indefinida (crearClave(), ver hallazgo
 * de la sesión de migración) y lo insertaba explícito. La app nueva sí deja que
 * MySQL lo genere.
 */
class FinCaja extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_caja';
    protected $primaryKey = 'cc_caja';
    public $timestamps = false;

    protected $guarded = [];

    public function detalle(): HasMany
    {
        return $this->hasMany(FinCajaDetalle::class, 'cc_caja', 'cc_caja');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(GenPersona::class, 'cc_persona', 'cc_persona');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(GenBanco::class, 'cc_banco', 'cc_banco');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(GenCuenta::class, 'cc_cuenta', 'cc_cuenta');
    }

    public function getVoucherAttribute(): string
    {
        return trim($this->ct_serie).'-'.trim($this->ct_numero);
    }
}
