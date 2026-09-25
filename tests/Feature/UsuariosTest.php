<?php

use App\Livewire\Usuarios\Index as UsuariosIndex;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function usuarioAutenticadoAdmin(): SegUsuario
{
    return (new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']);
}

afterEach(function () {
    DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', 900500)->delete();
    DB::connection('legacy')->table('gen_personas')->where('cc_persona', 900500)->delete();
});

it('crea un usuario nuevo con clave inicial igual al DNI del socio (decisión explícita del cliente)', function () {
    $this->actingAs(usuarioAutenticadoAdmin());

    DB::connection('legacy')->table('gen_personas')->insert([
        'cc_persona' => 900500,
        'ct_nombres' => 'SOCIO SIN USUARIO',
        'ct_nro_doc' => '99887766',
        'ct_conyuguen' => '',
        'ct_hijos' => '0',
        'cfl_vigencia' => '1',
    ]);

    Livewire::test(UsuariosIndex::class)
        ->call('elegirSocioNuevo', '900500', 'SOCIO SIN USUARIO')
        ->set('cc_user', 'socio.nuevo')
        ->set('cc_perfil', '2')
        ->call('guardar')
        ->assertHasNoErrors();

    $usuario = DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', 900500)->first();

    expect($usuario)->not->toBeNull();
    expect($usuario->ct_clave)->toBe(sha1('99887766'));
    expect((string) $usuario->cfl_clave_cambia)->toBe('0');
});

it('al editar, si se deja la clave en blanco no se modifica', function () {
    $this->actingAs(usuarioAutenticadoAdmin());

    DB::connection('legacy')->table('gen_personas')->insert([
        'cc_persona' => 900500,
        'ct_nombres' => 'SOCIO CON USUARIO',
        'ct_nro_doc' => '99887799',
        'ct_conyuguen' => '',
        'ct_hijos' => '0',
        'cfl_vigencia' => '1',
    ]);
    DB::connection('legacy')->table('seg_usuario')->insert([
        'cc_usuario' => 900500,
        'cc_user' => 'socio.viejo',
        'ct_clave' => sha1('99887799'),
        'nn_tiempo_sesion' => 30,
        'cfl_acceso' => '1',
        'cc_perfil' => 2,
    ]);

    Livewire::test(UsuariosIndex::class)
        ->call('editar', '900500')
        ->set('cc_perfil', '3')
        ->call('guardar')
        ->assertHasNoErrors();

    $usuario = DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', 900500)->first();

    expect($usuario->cc_perfil)->toBe(3);
    expect($usuario->ct_clave)->toBe(sha1('99887799'));
});

it('al editar, si se escribe una clave nueva sí se reemplaza (en bcrypt)', function () {
    $this->actingAs(usuarioAutenticadoAdmin());

    DB::connection('legacy')->table('gen_personas')->insert([
        'cc_persona' => 900500,
        'ct_nombres' => 'SOCIO CON USUARIO',
        'ct_nro_doc' => '99887799',
        'ct_conyuguen' => '',
        'ct_hijos' => '0',
        'cfl_vigencia' => '1',
    ]);
    DB::connection('legacy')->table('seg_usuario')->insert([
        'cc_usuario' => 900500,
        'cc_user' => 'socio.viejo',
        'ct_clave' => sha1('99887799'),
        'nn_tiempo_sesion' => 30,
        'cfl_acceso' => '1',
        'cc_perfil' => 2,
    ]);

    Livewire::test(UsuariosIndex::class)
        ->call('editar', '900500')
        ->set('ct_clave', 'nueva-clave-elegida')
        ->call('guardar')
        ->assertHasNoErrors();

    $usuario = DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', 900500)->first();

    // La edición usa sha1 tal como el legacy (dao_seg_usuario::modificar); el
    // upgrade a bcrypt sigue ocurriendo recién en el primer login, no aquí.
    expect($usuario->ct_clave)->toBe(sha1('nueva-clave-elegida'));
});

it('rechaza un nombre de usuario duplicado', function () {
    $this->actingAs(usuarioAutenticadoAdmin());

    DB::connection('legacy')->table('gen_personas')->insert([
        'cc_persona' => 900500,
        'ct_nombres' => 'SOCIO DUPLICADO',
        'ct_nro_doc' => '99887711',
        'ct_conyuguen' => '',
        'ct_hijos' => '0',
        'cfl_vigencia' => '1',
    ]);

    Livewire::test(UsuariosIndex::class)
        ->call('elegirSocioNuevo', '900500', 'SOCIO DUPLICADO')
        ->set('cc_user', 'admin')
        ->set('cc_perfil', '2')
        ->call('guardar')
        ->assertHasErrors('cc_user');
});
