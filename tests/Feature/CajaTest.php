<?php

use App\Livewire\Caja\ListaRecibos;
use App\Livewire\Caja\NuevoRecibo;
use App\Livewire\CargarDeuda\Index as CargarDeudaIndex;
use App\Models\Legacy\FinCaja;
use App\Models\Legacy\FinComprobante;
use App\Models\Legacy\FinEstadoCuenta;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function usuarioAutenticadoCaja(): SegUsuario
{
    return (new SegUsuario)->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']);
}

beforeEach(function () {
    $this->correlativoOriginal = FinComprobante::where('ct_serie', '001')->value('ct_correlativo');
});

afterEach(function () {
    DB::connection('legacy')->table('fin_estado_cuenta')->where('cc_descripcion', 'like', 'TEST %')->delete();
    $cajasTest = DB::connection('legacy')->table('fin_caja')->where('caj_obs', 'like', 'TEST %')->pluck('cc_caja');
    DB::connection('legacy')->table('fin_caja_detalle')->whereIn('cc_caja', $cajasTest)->delete();
    DB::connection('legacy')->table('fin_caja')->whereIn('cc_caja', $cajasTest)->delete();
    FinComprobante::where('ct_serie', '001')->update(['ct_correlativo' => $this->correlativoOriginal]);
});

it('emite un recibo con el número escrito por el cajero y crea el movimiento en estado de cuenta', function () {
    $this->actingAs(usuarioAutenticadoCaja());

    Livewire::test(NuevoRecibo::class)
        ->call('elegirSocio', '2', 'ACERO MIGUEL JORGE JOHN')
        ->set('ct_serie', '001')
        ->set('ct_numero', '99990001')
        ->set('pag_tipo', 'E')
        ->set('caj_obs', 'TEST recibo de prueba')
        ->set('nuevoConcepto', '1')
        ->set('nuevaCantidad', '2')
        ->set('nuevoImporte', '25')
        ->call('agregarLinea')
        ->assertHasNoErrors()
        ->call('guardar')
        ->assertHasNoErrors();

    $recibo = FinCaja::where('caj_obs', 'TEST recibo de prueba')->first();

    expect($recibo)->not->toBeNull();
    expect((int) $recibo->ct_numero)->toBe(99990001);
    expect($recibo->cc_persona)->toBe(2);
    expect((float) $recibo->detalle->first()->ct_total)->toBe(50.0);

    $movimiento = FinEstadoCuenta::where('cc_caja', $recibo->cc_caja)->first();
    expect($movimiento->ct_tipo)->toBe('P');
    expect((float) $movimiento->ct_monto)->toBe(-50.0);

    expect((int) FinComprobante::where('ct_serie', '001')->value('ct_correlativo'))->toBe(99990001);
});

it('sugiere el número siguiente al último recibo de la serie, sin chocar con uno vigente', function () {
    $this->actingAs(usuarioAutenticadoCaja());

    $sugerido = (int) Livewire::test(NuevoRecibo::class)
        ->set('ct_serie', '001')
        ->get('ct_numero');

    expect($sugerido)->toBeGreaterThan(0);
    expect(FinCaja::where('ct_serie', '001')->where('ct_numero', $sugerido)->where('ct_vigencia', '1')->exists())->toBeFalse();
});

it('rechaza un número que ya tiene un recibo vigente en la serie', function () {
    $this->actingAs(usuarioAutenticadoCaja());

    $ocupado = (int) FinCaja::where('ct_serie', '001')->where('ct_vigencia', '1')->value('ct_numero');
    $recibosAntes = FinCaja::count();

    Livewire::test(NuevoRecibo::class)
        ->call('elegirSocio', '2', 'ACERO MIGUEL JORGE JOHN')
        ->set('ct_serie', '001')
        ->set('ct_numero', (string) $ocupado)
        ->set('pag_tipo', 'E')
        ->set('caj_obs', 'TEST número repetido')
        ->set('nuevoConcepto', '1')
        ->set('nuevoImporte', '10')
        ->call('agregarLinea')
        ->call('guardar')
        ->assertHasErrors(['ct_numero']);

    expect(FinCaja::count())->toBe($recibosAntes);
});

it('anula un recibo replicando el criterio legacy: vigencia 0 y montos en cero', function () {
    $this->actingAs(usuarioAutenticadoCaja());

    Livewire::test(NuevoRecibo::class)
        ->call('elegirSocio', '2', 'ACERO MIGUEL JORGE JOHN')
        ->set('ct_serie', '001')
        ->set('pag_tipo', 'E')
        ->set('caj_obs', 'TEST recibo a anular')
        ->set('nuevoConcepto', '1')
        ->set('nuevaCantidad', '1')
        ->set('nuevoImporte', '30')
        ->call('agregarLinea')
        ->call('guardar');

    $recibo = FinCaja::where('caj_obs', 'TEST recibo a anular')->first();

    Livewire::test(ListaRecibos::class)->call('anular', $recibo->cc_caja);

    $recibo->refresh();
    expect($recibo->ct_vigencia)->toBe('0');
    expect((float) $recibo->detalle->first()->ct_total)->toBe(0.0);

    $movimiento = FinEstadoCuenta::where('cc_caja', $recibo->cc_caja)->first();
    expect($movimiento->ct_vigencia)->toBe('0');
    expect((float) $movimiento->ct_monto)->toBe(0.0);
});

it('carga una deuda a un socio puntual', function () {
    $this->actingAs(usuarioAutenticadoCaja());

    Livewire::test(CargarDeudaIndex::class)
        ->set('cc_persona', '2')
        ->set('cc_concepto', '1')
        ->set('cc_descripcion', 'TEST cuota extraordinaria')
        ->set('ct_monto', '75')
        ->call('guardar')
        ->assertHasNoErrors();

    $movimiento = FinEstadoCuenta::where('cc_descripcion', 'TEST cuota extraordinaria')->first();

    expect($movimiento->cc_persona)->toBe(2);
    expect($movimiento->ct_tipo)->toBe('D');
    expect((float) $movimiento->ct_monto)->toBe(75.0);
});
