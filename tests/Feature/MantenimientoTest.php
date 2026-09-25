<?php

use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Proveedores\Index as ProveedoresIndex;
use App\Livewire\Socios\Index as SociosIndex;
use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\GenEmpresa;
use App\Models\Legacy\GenPersona;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function usuarioDePruebaAutenticado(): SegUsuario
{
    return (new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']);
}

afterEach(function () {
    DB::connection('legacy')->table('gen_personas')->where('ct_nro_doc', '87654321')->delete();
    DB::connection('legacy')->table('fin_concepto')->where('ct_nombre', 'like', 'TEST %')->delete();
    DB::connection('legacy')->table('gen_empresa')->where('emp_ruc', 'like', '99%')->delete();
});

it('crea un socio nuevo con documento único y queda vigente', function () {
    $this->actingAs(usuarioDePruebaAutenticado());

    Livewire::test(SociosIndex::class)
        ->call('nuevo')
        ->set('ct_nombres', 'nuevo socio de prueba')
        ->set('cp_tipo_doc', '1')
        ->set('ct_nro_doc', '87654321')
        ->set('cp_sexo', 'M')
        ->call('guardar')
        ->assertHasNoErrors();

    $socio = GenPersona::where('ct_nro_doc', '87654321')->first();

    expect($socio)->not->toBeNull();
    expect($socio->ct_nombres)->toBe('NUEVO SOCIO DE PRUEBA');
    expect($socio->cfl_vigencia)->toEqual(1);
});

it('rechaza un socio con documento ya registrado', function () {
    $this->actingAs(usuarioDePruebaAutenticado());

    // MANUEL PRETEL (cc_persona=1) ya existe en el dump con DNI 40778180.
    Livewire::test(SociosIndex::class)
        ->call('nuevo')
        ->set('ct_nombres', 'OTRO SOCIO')
        ->set('cp_tipo_doc', '1')
        ->set('ct_nro_doc', '40778180')
        ->set('cp_sexo', 'M')
        ->call('guardar')
        ->assertHasErrors('ct_nro_doc');
});

it('crea un concepto y le agrega un subconcepto', function () {
    $this->actingAs(usuarioDePruebaAutenticado());

    Livewire::test(ConceptosIndex::class)
        ->call('nuevo')
        ->set('ct_tipo', 'I')
        ->set('ct_nombre', 'TEST CONCEPTO NUEVO')
        ->call('guardar')
        ->assertHasNoErrors();

    $concepto = FinConcepto::where('ct_nombre', 'TEST CONCEPTO NUEVO')->first();
    expect($concepto)->not->toBeNull();
    expect($concepto->ct_vigencia)->toBe('1');

    Livewire::test(ConceptosIndex::class)
        ->call('alternarSubconceptos', $concepto->cc_concepto)
        ->set('nuevoSubconceptoNombre', 'Sub de prueba')
        ->call('agregarSubconcepto', $concepto->cc_concepto)
        ->assertHasNoErrors();

    expect($concepto->subconceptos()->where('ct_nombre', 'Sub de prueba')->exists())->toBeTrue();
});

it('exige RUC de 11 dígitos y rechaza uno duplicado', function () {
    $this->actingAs(usuarioDePruebaAutenticado());

    Livewire::test(ProveedoresIndex::class)
        ->call('nuevo')
        ->set('emp_razon_social', 'PROVEEDOR DE PRUEBA')
        ->set('emp_ruc', '123')
        ->call('guardar')
        ->assertHasErrors('emp_ruc');

    Livewire::test(ProveedoresIndex::class)
        ->call('nuevo')
        ->set('emp_razon_social', 'PROVEEDOR DE PRUEBA')
        ->set('emp_ruc', '99999999999')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(GenEmpresa::where('emp_ruc', '99999999999')->exists())->toBeTrue();

    Livewire::test(ProveedoresIndex::class)
        ->call('nuevo')
        ->set('emp_razon_social', 'OTRO PROVEEDOR')
        ->set('emp_ruc', '99999999999')
        ->call('guardar')
        ->assertHasErrors('emp_ruc');
});
