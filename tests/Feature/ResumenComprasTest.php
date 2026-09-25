<?php

use App\Livewire\Compras\Index as ComprasIndex;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

/**
 * Oráculo real (SQL directo, año=2018): concepto 5 "SEGURIDAD" = 27625.03.
 */
it('calcula el resumen de egresos por concepto igual que bo_fin_compras::resumenCompras', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $componente = Livewire::test(ComprasIndex::class)
        ->call('alternarResumen')
        ->set('resumenAnho', '2018')
        ->call('buscarResumen');

    $fila = collect($componente->get('resumenFilas'))->firstWhere('cc_concepto', 5);

    expect((float) $fila->monto)->toBe(27625.03);
});

it('exporta el resumen de egresos a Excel', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(ComprasIndex::class)
        ->call('alternarResumen')
        ->set('resumenAnho', '2018')
        ->call('buscarResumen')
        ->call('exportarResumenExcel')
        ->assertFileDownloaded('resumen-egresos.xlsx');
});
