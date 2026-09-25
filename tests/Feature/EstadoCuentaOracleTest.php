<?php

use App\Services\EstadoCuentaService;

/**
 * Test oráculo (Fase 10 del plan de migración): compara el saldo calculado por el
 * servicio nuevo contra valores reales extraídos directamente de fin_estado_cuenta
 * (filtrando ct_vigencia='1') en la copia local del dump legacy `fortaleza_dev`.
 *
 * Estos valores NO deben actualizarse a mano para "hacer pasar" el test: si cambian,
 * es porque el dump de referencia cambió, y hay que re-extraerlos con la misma consulta.
 */
dataset('saldos_reales', [
    ['cc_persona' => '133', 'saldo_esperado' => '1490.00'],
    ['cc_persona' => '345', 'saldo_esperado' => '9090.00'],
    ['cc_persona' => '325', 'saldo_esperado' => '7400.00'],
    ['cc_persona' => '310', 'saldo_esperado' => '-640.00'],
    ['cc_persona' => '349', 'saldo_esperado' => '-2620.00'],
    ['cc_persona' => '202', 'saldo_esperado' => '7430.00'],
    ['cc_persona' => '269', 'saldo_esperado' => '7320.00'],
    ['cc_persona' => '66', 'saldo_esperado' => '40.00'],
]);

it('calcula el saldo del socio igual que el sistema legacy', function (string $cc_persona, string $saldo_esperado) {
    $servicio = new EstadoCuentaService();

    expect($servicio->saldo($cc_persona))->toBe($saldo_esperado);
})->with('saldos_reales');
