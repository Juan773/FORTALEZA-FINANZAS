<?php

use App\Livewire\Balances\Index as BalancesIndex;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

/**
 * Oráculos calculados directamente por SQL sobre la copia local del dump:
 *  - Balance por concepto=1, año<=2018 (sin mes): debe=1994000.00, haber=-1044688.00
 *  - Balance por socio, cc_persona=2 (DNI 41362897), activo: debe=11420.00, haber=-8100.00, saldo=3320.00
 */
it('calcula el balance por concepto igual que el sistema legacy', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $componente = Livewire::test(BalancesIndex::class)
        ->set('anho', '2018')
        ->call('buscarPorConcepto');

    $fila = collect($componente->get('filas'))->firstWhere('cc_concepto', '0001');

    expect((float) $fila['debe'])->toBe(1994000.0);
    expect((float) $fila['haber'])->toBe(-1044688.0);
});

it('calcula el balance por socio igual que el sistema legacy', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $componente = Livewire::test(BalancesIndex::class)
        ->call('cambiarPestana', 'socio')
        ->set('cfl_vigencia', '1')
        ->call('buscarPorSocio');

    $fila = collect($componente->get('filas'))->firstWhere('ct_nro_doc', '41362897');

    expect((float) $fila->debe)->toBe(11420.0);
    expect((float) $fila->haber)->toBe(-8100.0);
    expect((float) $fila->saldo)->toBe(3320.0);
});
