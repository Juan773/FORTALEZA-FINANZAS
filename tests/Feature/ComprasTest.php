<?php

use App\Livewire\Compras\Index as ComprasIndex;
use App\Models\Legacy\FinCompras;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

afterEach(function () {
    DB::connection('legacy')->table('fin_compras')->where('ct_glosa', 'like', 'TEST %')->delete();
});

it('registra un egreso nuevo con fecha de registro automática y vigente', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    // 4 = "CARRETERA" (concepto de tipo Egreso vigente en el dump).
    Livewire::test(ComprasIndex::class)
        ->call('nuevo')
        ->set('emp_ruc', '20123456789')
        ->set('emp_razon_social', 'FERRETERIA DE PRUEBA')
        ->set('ct_comprobante', 'FC')
        ->set('cc_concepto', '4')
        ->set('ct_glosa', 'TEST compra de materiales')
        ->set('ct_monto', '150.50')
        ->call('guardar')
        ->assertHasNoErrors();

    $compra = FinCompras::where('ct_glosa', 'TEST compra de materiales')->first();

    expect($compra)->not->toBeNull();
    expect($compra->ct_vigencia)->toBe('1');
    expect((float) $compra->ct_monto)->toBe(150.50);
    expect($compra->ct_fecha)->not->toBeNull();
});

it('exige RUC de 11 dígitos, concepto y monto mayor a cero', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(ComprasIndex::class)
        ->call('nuevo')
        ->set('emp_ruc', '123')
        ->set('emp_razon_social', 'X')
        ->set('ct_comprobante', 'FC')
        ->set('ct_glosa', 'TEST sin concepto')
        ->set('ct_monto', '0')
        ->call('guardar')
        ->assertHasErrors(['emp_ruc', 'cc_concepto', 'ct_monto']);
});
