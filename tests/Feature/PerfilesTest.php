<?php

use App\Livewire\Perfiles\Index as PerfilesIndex;
use App\Models\Legacy\SegPerfil;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

afterEach(function () {
    DB::connection('legacy')->table('seg_perfil')->where('ct_perfil', 'TEST Perfil Nuevo')->delete();
});

it('crea un perfil nuevo con id generado de forma segura', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(PerfilesIndex::class)
        ->call('nuevo')
        ->set('ct_perfil', 'TEST Perfil Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    $perfil = SegPerfil::where('ct_perfil', 'TEST Perfil Nuevo')->first();

    expect($perfil)->not->toBeNull();
    expect($perfil->cfl_vigencia)->toEqual(1);
});

it('edita un perfil existente', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $perfil = SegPerfil::first();
    $nombreOriginal = $perfil->ct_perfil;

    Livewire::test(PerfilesIndex::class)
        ->call('editar', $perfil->cc_perfil)
        ->set('ct_perfil', 'TEST Perfil Nuevo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(SegPerfil::find($perfil->cc_perfil)->ct_perfil)->toBe('TEST Perfil Nuevo');

    // Restaurar para no dejar sucio el perfil real que se editó.
    SegPerfil::where('cc_perfil', $perfil->cc_perfil)->update(['ct_perfil' => $nombreOriginal]);
});
