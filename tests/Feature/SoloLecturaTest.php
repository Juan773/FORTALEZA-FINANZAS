<?php

use App\Livewire\Caja\ListaRecibos;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Socios\Index as SociosIndex;
use App\Models\Legacy\SegPerfil;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

/**
 * Perfil "Solo lectura": consulta todo, pero no crea, edita ni anula nada.
 * El perfil se arma en memoria (setRelation) para no insertar en la BD legacy,
 * que es la copia real del dump y no se refresca entre tests.
 */
function usuarioDePerfil(string $nombrePerfil): SegUsuario
{
    return (new SegUsuario)
        ->forceFill(['cc_usuario' => '370', 'cc_user' => 'test'])
        ->setRelation('perfil', new SegPerfil(['ct_perfil' => $nombrePerfil]));
}

it('deja consultar las pantallas de lectura al perfil Solo lectura', function () {
    $this->actingAs(usuarioDePerfil('Solo lectura'));

    $this->get('/socios')->assertOk();
    $this->get('/caja')->assertOk();
    $this->get('/estado-cuenta')->assertOk();
    $this->get('/balances')->assertOk();
});

it('bloquea con 403 las pantallas de registro al perfil Solo lectura', function () {
    $this->actingAs(usuarioDePerfil('Solo lectura'));

    $this->get('/caja/nuevo')->assertForbidden();
    $this->get('/cargar-deuda')->assertForbidden();
});

it('bloquea en el servidor las acciones que modifican datos aunque se llamen a mano', function () {
    $this->actingAs(usuarioDePerfil('Solo lectura'));

    Livewire::test(SociosIndex::class)->call('nuevo')->assertForbidden();
    Livewire::test(SociosIndex::class)->call('guardar')->assertForbidden();
    Livewire::test(ConceptosIndex::class)->call('quitarSubconcepto', 1)->assertForbidden();
    Livewire::test(ListaRecibos::class)->call('anular', 1)->assertForbidden();
});

it('reconoce el perfil aunque esté escrito con otras mayúsculas o espacios', function () {
    expect(usuarioDePerfil('  SOLO LECTURA ')->esSoloLectura())->toBeTrue();
    expect(usuarioDePerfil('Cajero')->esSoloLectura())->toBeFalse();
});

it('no le quita nada a los demás perfiles', function () {
    $this->actingAs(usuarioDePerfil('Cajero'));

    $this->get('/caja/nuevo')->assertOk();
    $this->get('/cargar-deuda')->assertOk();
    Livewire::test(SociosIndex::class)->call('nuevo')->assertSet('mostrandoFormulario', true);
});
