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

/**
 * Oráculo real: mismo socio (cc_persona=2), pero "todos los años" (anho=''):
 * SUM(ct_monto) vigente sobre TODOS los años = 3320.00 (26 movimientos).
 * Sin filtro de año no aplica "saldo anterior" (ya está todo incluido en el detalle).
 */
it('permite buscar sin filtrar por año (todos los años)', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $componente = Livewire::test(EstadoCuentaIndex::class)
        ->set('anho', '')
        ->set('ct_nro_doc', '41362897')
        ->call('buscar')
        ->assertHasNoErrors();

    expect($componente->get('movimientos'))->toHaveCount(26);
    expect((float) $componente->get('totalDebito') + (float) $componente->get('totalCredito'))->toBe(3320.0);
    expect((float) $componente->get('saldoAnterior'))->toBe(0.0);
    expect((float) $componente->get('saldoFinal'))->toBe(3320.0);
});

it('sugiere socios por nombre mientras se escribe y permite elegir uno', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(EstadoCuentaIndex::class)
        ->set('buscarSocio', 'ACERO MIGUEL')
        ->assertSet('ct_nro_doc', '')
        ->call('elegirSocio', '000002')
        ->assertSet('ct_nro_doc', '41362897')
        ->assertSet('buscarSocio', '');
});

/**
 * Regresión real detectada en producción: el middleware ConvertEmptyStringsToNull
 * convierte anho='' en null (conservando la clave), así que $request->query('anho',
 * $default) NO debía usarse — devolvía el default (año actual) en vez de reconocer
 * "todos los años", y el PDF terminaba filtrando por el año actual sin que se notara
 * en la pantalla (ahí Livewire sí lo manejaba bien). El nombre del archivo delata cuál
 * lógica se ejecutó: "-todos.pdf" si reconoce el filtro vacío, "-2026.pdf" si no.
 */
it('el PDF de estado de cuenta respeta "todos los años" cuando anho llega vacío', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    $response = $this->get('/estado-cuenta/pdf?'.http_build_query([
        'anho' => '',
        'ct_nro_doc' => '41362897',
        'cc_concepto' => '',
    ]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('estado-cuenta-41362897-todos.pdf');
});

/**
 * Caso real: Felicita Hilasaca Portillo no tiene registro propio, está solo
 * como cónyuge del socio Moisés Minaya Pampa (cc_persona=000093, DNI 07282576).
 * Buscarla por su apellido debe encontrar la cuenta del socio, marcada como
 * coincidencia por cónyuge.
 */
it('encuentra al socio cuando se busca por el nombre de su cónyuge', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    // Minúsculas/mayúsculas mixtas a propósito (así lo escribió el usuario real):
    // el dato está guardado en mayúsculas, la búsqueda debe ser insensible a esto
    // (nota: este caso puntual pasa igual en MySQL local sin el fix — el bug real
    // era la colación latin1_bin de TiDB en producción, ver comentario en
    // Socios\Index::consultaFiltrada — pero igual se deja así por ser el input real).
    $componente = Livewire::test(EstadoCuentaIndex::class)
        ->set('buscarSocio', 'Hilasaca');

    $resultados = $componente->instance()->resultadosBusquedaSocios();

    expect($resultados)->toHaveCount(1);
    expect($resultados->first()->ct_nro_doc)->toBe('07282576');
    expect($componente->instance()->coincidePorConyugue($resultados->first()))->toBeTrue();
});

it('muestra un mensaje cuando el documento no existe', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(EstadoCuentaIndex::class)
        ->set('ct_nro_doc', '99999999')
        ->call('buscar')
        ->assertHasErrors('ct_nro_doc');
});
