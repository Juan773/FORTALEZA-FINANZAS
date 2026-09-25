<?php

use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Livewire\Caja\ListaRecibos;
use App\Livewire\Caja\NuevoRecibo;
use App\Models\Auditoria;
use App\Models\Legacy\FinCaja;
use App\Models\Legacy\FinComprobante;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->correlativoOriginal = FinComprobante::where('ct_serie', '001')->value('ct_correlativo');
});

afterEach(function () {
    $cajasTest = DB::connection('legacy')->table('fin_caja')->where('caj_obs', 'like', 'TEST AUDIT %')->pluck('cc_caja');
    DB::connection('legacy')->table('fin_estado_cuenta')->whereIn('cc_caja', $cajasTest)->delete();
    DB::connection('legacy')->table('fin_caja_detalle')->whereIn('cc_caja', $cajasTest)->delete();
    DB::connection('legacy')->table('fin_caja')->whereIn('cc_caja', $cajasTest)->delete();
    FinComprobante::where('ct_serie', '001')->update(['ct_correlativo' => $this->correlativoOriginal]);
});

it('registra en auditoría la emisión y anulación de un recibo', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(NuevoRecibo::class)
        ->call('elegirSocio', '2', 'ACERO MIGUEL JORGE JOHN')
        ->set('ct_serie', '001')
        ->set('pag_tipo', 'E')
        ->set('caj_obs', 'TEST AUDIT recibo')
        ->set('nuevoConcepto', '1')
        ->set('nuevaCantidad', '1')
        ->set('nuevoImporte', '20')
        ->call('agregarLinea')
        ->call('guardar');

    $recibo = FinCaja::where('caj_obs', 'TEST AUDIT recibo')->first();

    expect(Auditoria::where('accion', 'caja.emitir')->whereJsonContains('datos->cc_caja', $recibo->cc_caja)->exists())
        ->toBeTrue();

    Livewire::test(ListaRecibos::class)->call('anular', $recibo->cc_caja);

    expect(Auditoria::where('accion', 'caja.anular')->whereJsonContains('datos->cc_caja', $recibo->cc_caja)->exists())->toBeTrue();
});

it('muestra la pantalla de auditoría sin errores, con fechas ya formateadas', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Auditoria::registrar('caja.emitir', 'TEST registro de prueba', ['cc_caja' => 1]);

    Livewire::test(AuditoriaIndex::class)
        ->assertOk()
        ->assertSee('TEST registro de prueba');

    Auditoria::where('descripcion', 'TEST registro de prueba')->delete();
});
