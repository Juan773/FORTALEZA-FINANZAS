<?php

use App\Support\NumeroALetras;

it('convierte montos a letras en español', function (float $numero, string $esperado) {
    expect(NumeroALetras::convertir($numero))->toBe($esperado);
})->with([
    [0, 'Cero con 00/100'],
    [1, 'Uno con 00/100'],
    [21, 'Veintiuno con 00/100'],
    [100, 'Cien con 00/100'],
    [180, 'Ciento ochenta con 00/100'],
    [1180, 'Mil ciento ochenta con 00/100'],
    [1000, 'Mil con 00/100'],
    [2620.5, 'Dos mil seiscientos veinte con 50/100'],
    [1000000, 'Un millón con 00/100'],
    [750, 'Setecientos cincuenta con 00/100'],
]);
