<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Prueba el login contra la vista legacy vt_empleado_usuario, usando un socio
 * y usuario DE PRUEBA creados y borrados dentro del propio test (no se usan
 * credenciales reales de producción, que además no conocemos en texto plano).
 * Replica exactamente bo_seg_usuario::validar(): SHA-1, cfl_acceso, cfl_vigencia.
 *
 * cc_persona es int(6) zerofill en la BD legacy, así que los IDs de prueba usan
 * un rango alto (900000+) que no choca con socios reales (394 filas, ids bajos).
 */
function crearSocioYUsuarioDePrueba(int $ccPersona, array $overridesUsuario = []): void
{
    DB::connection('legacy')->table('gen_personas')->insert([
        'cc_persona' => $ccPersona,
        'ct_nombres' => 'SOCIO DE PRUEBA',
        'ct_conyuguen' => '',
        'ct_hijos' => '0',
        'cfl_vigencia' => $overridesUsuario['cfl_vigencia_persona'] ?? '1',
    ]);

    DB::connection('legacy')->table('seg_usuario')->insert(array_merge([
        'cc_usuario' => $ccPersona,
        'cc_user' => 'test_'.$ccPersona,
        'ct_clave' => sha1('clave-legacy-123'),
        'nn_tiempo_sesion' => 30,
        'cfl_acceso' => '1',
        'cc_perfil' => 1,
    ], collect($overridesUsuario)->except('cfl_vigencia_persona')->all()));
}

afterEach(function () {
    // Rango acotado a los fixtures de ESTE archivo (900001-900005): un rango abierto
    // (">= 900000") borraba también usuarios de demo/revisión creados manualmente
    // con códigos altos (ej. 999998), que no tienen nada que ver con este test.
    DB::connection('legacy')->table('seg_usuario')->whereBetween('cc_usuario', [900001, 900005])->delete();
    DB::connection('legacy')->table('gen_personas')->whereBetween('cc_persona', [900001, 900005])->delete();
});

it('permite el login con la clave correcta y hace upgrade del hash a bcrypt', function () {
    crearSocioYUsuarioDePrueba(900001);

    $this->post('/login', ['cc_user' => 'test_900001', 'ct_clave' => 'clave-legacy-123'])
        ->assertRedirect('/home');

    $this->assertAuthenticated();

    $hashActual = DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', 900001)->value('ct_clave');
    expect($hashActual)->not->toBe(sha1('clave-legacy-123'));
    expect(Hash::check('clave-legacy-123', $hashActual))->toBeTrue();
});

it('rechaza una clave incorrecta', function () {
    crearSocioYUsuarioDePrueba(900002);

    $this->post('/login', ['cc_user' => 'test_900002', 'ct_clave' => 'clave-equivocada'])
        ->assertSessionHasErrors('cc_user');

    $this->assertGuest();
});

it('rechaza a un usuario con acceso deshabilitado (cfl_acceso=0)', function () {
    crearSocioYUsuarioDePrueba(900003, ['cfl_acceso' => '0']);

    $this->post('/login', ['cc_user' => 'test_900003', 'ct_clave' => 'clave-legacy-123'])
        ->assertSessionHasErrors('cc_user');

    $this->assertGuest();
});

it('rechaza a un socio inactivo (cfl_vigencia=0) aunque el acceso esté habilitado', function () {
    crearSocioYUsuarioDePrueba(900004, ['cfl_vigencia_persona' => '0']);

    $this->post('/login', ['cc_user' => 'test_900004', 'ct_clave' => 'clave-legacy-123'])
        ->assertSessionHasErrors('cc_user');

    $this->assertGuest();
});

it('ya migrado a hash bcrypt (segundo login) valida sin volver a re-hashear', function () {
    crearSocioYUsuarioDePrueba(900005, ['ct_clave' => Hash::make('clave-nueva-bcrypt')]);

    $this->post('/login', ['cc_user' => 'test_900005', 'ct_clave' => 'clave-nueva-bcrypt'])
        ->assertRedirect('/home');

    $this->assertAuthenticated();
});
