<?php

use App\Exports\SociosExport;
use App\Models\Legacy\GenPersona;

/**
 * Oráculo real: socio DNI 41362897 (cc_persona=000002), valores leídos
 * directamente por SQL sobre la copia local del dump (sin decodificar,
 * tal como están almacenados en gen_personas).
 */
it('exporta todas las columnas de gen_personas tal como están en la base de datos', function () {
    $socio = GenPersona::where('ct_nro_doc', '41362897')->firstOrFail();

    $export = new SociosExport(collect([$socio]));

    expect($export->headings())->toHaveCount(23);

    $fila = $export->collection()->first();

    expect($fila)->toHaveCount(23);
    expect($fila[0])->toBe('000002'); // cc_persona formateado
    expect($fila[1])->toBe('2013-08-25 00:00:00'); // ct_fec_reg
    expect($fila[2])->toBe('02'); // ct_tp_user
    expect($fila[3])->toBe('ACERO MIGUEL  JORGE JOHN'); // ct_nombres (tal cual, con doble espacio real)
    expect($fila[16])->toBe('41362897'); // ct_nro_doc
    expect($fila[17])->toBe('M'); // cp_sexo
    expect($fila[13])->toBe('01'); // ct_zona
    expect($fila[21])->toBe(1); // cfl_vigencia
});
