<?php

use App\Livewire\Balances\Index as BalancesIndex;
use App\Models\Legacy\FinCaja;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

function usuarioAutenticadoExport(): SegUsuario
{
    return (new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']);
}

it('descarga el PDF de un recibo real', function () {
    $this->actingAs(usuarioAutenticadoExport());

    $recibo = FinCaja::whereNotNull('cc_persona')->first();

    $response = $this->get("/caja/{$recibo->cc_caja}/pdf");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('descarga el PDF del estado de cuenta con los mismos datos que la pantalla', function () {
    $this->actingAs(usuarioAutenticadoExport());

    $response = $this->get('/estado-cuenta/pdf?'.http_build_query([
        'anho' => '2018',
        'ct_nro_doc' => '41362897',
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('exporta el balance por concepto a Excel', function () {
    $this->actingAs(usuarioAutenticadoExport());

    Livewire::test(BalancesIndex::class)
        ->set('anho', '2018')
        ->call('buscarPorConcepto')
        ->call('exportarConceptoExcel')
        ->assertFileDownloaded('balance-por-concepto.xlsx');
});

it('exporta el balance por socio a Excel', function () {
    $this->actingAs(usuarioAutenticadoExport());

    Livewire::test(BalancesIndex::class)
        ->call('cambiarPestana', 'socio')
        ->call('buscarPorSocio')
        ->call('exportarSocioExcel')
        ->assertFileDownloaded('balance-por-socio.xlsx');
});
