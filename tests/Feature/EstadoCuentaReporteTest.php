<?php

use App\Livewire\EstadoCuenta\Index as EstadoCuentaIndex;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

/**
 * Oráculo real: socio DNI 41362897 (cc_persona=2, ACERO MIGUEL JORGE JOHN),
 * calculado directamente por SQL sobre la copia local del dump:
 *   - movimientos vigentes de 2018:            SUM(ct_monto) = 240.00
 *   - saldo acumulado hasta 2017 (sin filtrar): SUM(ct_monto) = 510.00
 *   - saldo final esperado = 240 + 510 = 750.00
 */
it('calcula el saldo final del reporte igual que el sistema legacy', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $componente = Livewire::test(EstadoCuentaIndex::class)
        ->set('anho', '2018')
        ->set('ct_nro_doc', '41362897')
        ->call('buscar')
        ->assertHasNoErrors();

    expect((float) $componente->get('saldoFinal'))->toBe(750.0);
    expect((float) $componente->get('totalDebito') + (float) $componente->get('totalCredito'))->toBe(240.0);
    expect((float) $componente->get('saldoAnterior'))->toBe(510.0);
});

it('muestra un mensaje cuando el documento no existe', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(EstadoCuentaIndex::class)
        ->set('ct_nro_doc', '99999999')
        ->call('buscar')
        ->assertHasErrors('ct_nro_doc');
});
