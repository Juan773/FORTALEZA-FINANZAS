<?php

use App\Models\Legacy\FinCaja;
use App\Models\Legacy\FinConcepto;

/**
 * Blindaje contra una regresión sutil: las columnas int(N) zerofill (cc_persona
 * en gen_personas, cc_concepto en fin_concepto/fin_subconcepto) llegan de PDO
 * como strings con ceros a la izquierda ("000001"), mientras que las columnas
 * hijas que las referencian son int normal (llegan como entero PHP). Eloquent
 * solo autocastea la PK a su keyType cuando $incrementing=true (el default);
 * los modelos con $incrementing=false (porque de verdad no son autoincrement
 * en la BD) pierden ese autocast y las relaciones belongsTo/hasMany quedan en
 * null en silencio, sin ningún error visible. Ver GenPersona/FinSubconcepto.
 */
it('resuelve la relación persona() de un recibo aunque cc_persona sea zerofill', function () {
    $caja = FinCaja::with('persona')->whereNotNull('cc_persona')->first();

    expect($caja->persona)->not->toBeNull();
    expect($caja->persona->ct_nombres)->not->toBeNull();
});

it('resuelve los subconceptos de un concepto aunque cc_concepto sea zerofill', function () {
    // Concepto 4 = CARRETERA, tiene subconceptos reales en el dump (Gasolina 90, Petróleo).
    $concepto = FinConcepto::with('subconceptos')->find(4);

    expect($concepto->subconceptos)->not->toBeEmpty();
});
