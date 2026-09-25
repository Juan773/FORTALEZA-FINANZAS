<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinSubconcepto extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_subconcepto';
    protected $primaryKey = 'cc_subconcepto';
    public $timestamps = false;

    protected $guarded = [];

    /**
     * cc_concepto aquí es `int(4) zerofill` (llega de PDO como "0004"), mientras
     * que fin_concepto.cc_concepto (su propia PK) se autocastea a entero por
     * defecto de Eloquent. Sin este cast la relación concepto() y el hasMany
     * inverso FinConcepto::subconceptos() no encontraban coincidencia (por eso
     * el contador de subconceptos mostraba "(0)" aunque sí existieran).
     */
    protected function casts(): array
    {
        return ['cc_concepto' => 'integer'];
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(FinConcepto::class, 'cc_concepto', 'cc_concepto');
    }
}
