<?php

use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;

/**
 * Etapa 9: verifica el balance por concepto (acumulado hasta 2025, el año real
 * más reciente en los datos — 2103 es un typo de digitación real que encontré
 * en un registro y se excluye porque ningún filtro de año realista lo alcanza)
 * contra TODOS los conceptos con movimientos, no solo el que usé como oráculo.
 */
it('el balance por concepto coincide con SQL directo para todos los conceptos', function () {
    $balance = app(BalanceService::class);

    $filasServicio = collect($balance->porConcepto('2025'))->keyBy('cc_concepto');

    $filasDirectas = DB::connection('legacy')->table('fin_estado_cuenta')
        ->select('cc_concepto')
        ->selectRaw("SUM(CASE WHEN ct_tipo='D' THEN ct_monto ELSE 0 END) as debe")
        ->selectRaw("SUM(CASE WHEN ct_tipo='P' THEN ct_monto ELSE 0 END) as haber")
        ->whereYear('ct_fecha', '<=', 2025)
        ->groupBy('cc_concepto')
        ->get();

    $discrepancias = [];

    foreach ($filasDirectas as $filaDirecta) {
        $codigo = $filaDirecta->cc_concepto;
        $filaServicio = $filasServicio[$codigo] ?? null;

        $debeServicio = round((float) ($filaServicio['debe'] ?? 0), 2);
        $haberServicio = round((float) ($filaServicio['haber'] ?? 0), 2);
        $debeDirecto = round((float) $filaDirecta->debe, 2);
        $haberDirecto = round((float) $filaDirecta->haber, 2);

        if ($debeServicio !== $debeDirecto || $haberServicio !== $haberDirecto) {
            $discrepancias[] = "concepto {$codigo}: servicio(debe={$debeServicio},haber={$haberServicio}) vs directo(debe={$debeDirecto},haber={$haberDirecto})";
        }
    }

    expect($discrepancias)->toBe([]);
})->group('reconciliacion');
