<?php

use App\Services\BalanceService;
use App\Services\EstadoCuentaService;
use Illuminate\Support\Facades\DB;

/**
 * Etapa 9 del plan de migración: verificación comparativa a escala completa,
 * no solo con la muestra puntual de la Etapa 0. Recalcula el saldo de LOS 394
 * SOCIOS REALES por dos caminos independientes (el servicio nuevo vs. SQL
 * directo) y exige que cuadren al centavo. Si algún socio con datos "raros"
 * (fechas nulas, tipos inesperados, etc.) rompiera la fórmula, esto lo atrapa
 * aquí en vez de en producción.
 */
it('el saldo de TODOS los socios coincide entre el servicio nuevo y SQL directo', function () {
    $estadoCuenta = app(EstadoCuentaService::class);

    $saldosDirectos = DB::connection('legacy')->table('fin_estado_cuenta')
        ->select('cc_persona')
        ->selectRaw('SUM(ct_monto) as saldo')
        ->where('ct_vigencia', '1')
        ->whereNotNull('cc_persona')
        ->groupBy('cc_persona')
        ->get()
        ->keyBy('cc_persona');

    expect($saldosDirectos)->not->toBeEmpty();

    $discrepancias = [];

    foreach ($saldosDirectos as $ccPersona => $fila) {
        $saldoServicio = round((float) $estadoCuenta->saldo((string) $ccPersona), 2);
        $saldoDirecto = round((float) $fila->saldo, 2);

        if ($saldoServicio !== $saldoDirecto) {
            $discrepancias[] = "socio {$ccPersona}: servicio={$saldoServicio} vs directo={$saldoDirecto}";
        }
    }

    expect($discrepancias)->toBe([]);
})->group('reconciliacion');

it('el balance por socio (asociados) coincide con SQL directo para todos los socios con movimientos', function () {
    $balance = app(BalanceService::class);

    $filasServicio = $balance->porSocio()->keyBy('ct_nro_doc');

    $filasDirectas = DB::connection('legacy')->table('fin_estado_cuenta as e')
        ->join('gen_personas as p', 'p.cc_persona', '=', 'e.cc_persona')
        ->select('p.ct_nro_doc')
        ->selectRaw("SUM(CASE WHEN e.ct_tipo='D' THEN e.ct_monto ELSE 0 END) + SUM(CASE WHEN e.ct_tipo='P' THEN e.ct_monto ELSE 0 END) as saldo")
        ->groupBy('p.ct_nombres', 'p.ct_nro_doc')
        ->get()
        ->keyBy('ct_nro_doc');

    $discrepancias = [];

    foreach ($filasDirectas as $doc => $filaDirecta) {
        $saldoServicio = round((float) ($filasServicio[$doc]->saldo ?? null), 2);
        $saldoDirecto = round((float) $filaDirecta->saldo, 2);

        if ($saldoServicio !== $saldoDirecto) {
            $discrepancias[] = "DNI {$doc}: servicio={$saldoServicio} vs directo={$saldoDirecto}";
        }
    }

    expect($discrepancias)->toBe([]);
})->group('reconciliacion');
