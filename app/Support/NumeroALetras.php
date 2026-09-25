<?php

namespace App\Support;

/**
 * Convierte un monto a letras en español, para el reporte de Estado de Cuenta
 * ("Usted tiene una deuda de ... soles"), igual que el legacy util/numeroLetra.php.
 *
 * NO es un puerto literal: se probó la función original (num2letras) corriéndola
 * directamente y en PHP 8 produce resultados incorrectos ("40" -> "Cientos cuarenta",
 * "1000000" -> "Cientos un mill&oacute;n") por errores de índices de array ya presentes
 * en el código original — solo "funcionaban por casualidad" bajo las reglas más laxas
 * de PHP 5. No tiene sentido replicar un bug de una frase decorativa de un reporte;
 * esto es una implementación correcta desde cero, con el mismo formato de salida
 * ("... con NN/100").
 */
class NumeroALetras
{
    private const UNIDADES = [
        '', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
        'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve',
    ];

    private const DECENAS = [
        2 => 'veinte', 3 => 'treinta', 4 => 'cuarenta', 5 => 'cincuenta',
        6 => 'sesenta', 7 => 'setenta', 8 => 'ochenta', 9 => 'noventa',
    ];

    private const CENTENAS = [
        1 => 'ciento', 2 => 'doscientos', 3 => 'trescientos', 4 => 'cuatrocientos', 5 => 'quinientos',
        6 => 'seiscientos', 7 => 'setecientos', 8 => 'ochocientos', 9 => 'novecientos',
    ];

    public static function convertir(float|string $numero): string
    {
        $numero = round((float) $numero, 2);
        $negativo = $numero < 0;
        $numero = abs($numero);

        $entero = (int) floor($numero);
        $centavos = (int) round(($numero - $entero) * 100);

        $texto = $entero === 0 ? 'cero' : self::milesOMas($entero);
        $texto = ($negativo ? 'menos ' : '').ucfirst($texto);

        return sprintf('%s con %02d/100', $texto, $centavos);
    }

    private static function milesOMas(int $n): string
    {
        if ($n >= 1_000_000) {
            $millones = intdiv($n, 1_000_000);
            $resto = $n % 1_000_000;
            $prefijo = $millones === 1 ? 'un millón' : self::centenasOMenos($millones).' millones';

            return trim($prefijo.($resto > 0 ? ' '.self::milesOMas($resto) : ''));
        }

        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefijo = $miles === 1 ? 'mil' : self::centenasOMenos($miles).' mil';

            return trim($prefijo.($resto > 0 ? ' '.self::centenasOMenos($resto) : ''));
        }

        return self::centenasOMenos($n);
    }

    private static function centenasOMenos(int $n): string
    {
        if ($n === 100) {
            return 'cien';
        }

        if ($n >= 100) {
            return trim(self::CENTENAS[intdiv($n, 100)].' '.self::decenasOMenos($n % 100));
        }

        return self::decenasOMenos($n);
    }

    private static function decenasOMenos(int $n): string
    {
        if ($n < 20) {
            return self::UNIDADES[$n];
        }

        if ($n < 30) {
            return $n === 20 ? 'veinte' : 'veinti'.self::UNIDADES[$n - 20];
        }

        $decena = intdiv($n, 10);
        $resto = $n % 10;

        return trim(self::DECENAS[$decena].($resto > 0 ? ' y '.self::UNIDADES[$resto] : ''));
    }
}
