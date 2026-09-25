<?php

use App\Models\Legacy\SegUsuario;

/**
 * Fase 12 (seguridad): el legacy nunca verificaba el perfil en el servidor
 * (s_validar_pagina() solo miraba la sesión), así que cualquier usuario logueado
 * podía llegar a Usuarios/Perfiles/Auditoría escribiendo la URL. Esta es una
 * restricción NUEVA que solo bloquea, no le quita nada a nadie que ya lo usara.
 */
function usuarioConPerfil(string $ccUsuario, int $ccPerfil): SegUsuario
{
    return (new SegUsuario())->forceFill(['cc_usuario' => $ccUsuario, 'cc_user' => 'test', 'cc_perfil' => $ccPerfil]);
}

it('permite el acceso a Usuarios/Perfiles/Auditoría solo al perfil Administrador', function () {
    // cc_perfil=1 es "Administrador" en los datos reales.
    $this->actingAs(usuarioConPerfil('1', 1));

    $this->get('/usuarios')->assertOk();
    $this->get('/perfiles')->assertOk();
    $this->get('/auditoria')->assertOk();
});

it('rechaza con 403 a perfiles distintos de Administrador', function () {
    // cc_perfil=4 es "Cajero" en los datos reales.
    $this->actingAs(usuarioConPerfil('370', 4));

    $this->get('/usuarios')->assertForbidden();
    $this->get('/perfiles')->assertForbidden();
    $this->get('/auditoria')->assertForbidden();
});

it('no restringe las pantallas operativas normales para otros perfiles', function () {
    $this->actingAs(usuarioConPerfil('370', 4));

    $this->get('/socios')->assertOk();
    $this->get('/caja')->assertOk();
});
