<?php

namespace App\Services;

use App\Models\Legacy\FinEstadoCuenta;
use App\Models\Legacy\GenPersona;
use Illuminate\Support\Collection;

/**
 * Replica la regla de negocio legacy (bo_fin_estado_cuenta.php / bo_fin_caja.php):
 * saldo del socio = suma algebraica de ct_monto en fin_estado_cuenta, filtrando
 * solo movimientos vigentes (ct_vigencia='1'). No usa fn_deuda() de la BD porque
 * esa función no filtra vigencia y no es invocada por ninguna pantalla actual
 * (ver docs/DICCIONARIO_DATOS.md y el hallazgo documentado en la sesión de migración).
 */
class EstadoCuentaService
{
    public function saldo(string $ccPersona): string
    {
        return (string) FinEstadoCuenta::query()
            ->vigente()
            ->where('cc_persona', $ccPersona)
            ->sum('ct_monto');
    }

    /**
     * Movimientos del socio en un año (reemplaza bo_fin_caja::EstadoCuenta()).
     * Filtra vigencia='1'. Si $ccConcepto viene vacío, no filtra por concepto.
     */
    public function movimientos(string $ccPersona, ?string $anho = null, ?string $ccConcepto = null): Collection
    {
        return FinEstadoCuenta::query()
            ->with(['persona', 'caja', 'concepto'])
            ->vigente()
            ->where('cc_persona', $ccPersona)
            ->when($anho, fn ($q) => $q->whereYear('ct_fecha', $anho))
            ->when($ccConcepto, fn ($q) => $q->where('cc_concepto', $ccConcepto))
            ->orderByDesc('ct_fecha')
            ->get()
            ->map(fn (FinEstadoCuenta $mov) => [
                'ct_fecha' => $mov->ct_fecha,
                'voucher' => $mov->caja?->voucher ?? '',
                'tipo' => $mov->ct_tipo === 'P' ? 'I' : 'E',
                'concepto' => $mov->concepto?->ct_nombre ?? '',
                'descripcion' => $mov->cc_descripcion,
                'ingreso' => $mov->ct_monto < 0 ? $mov->ct_monto : 0,
                'egreso' => $mov->ct_monto > 0 ? $mov->ct_monto : 0,
            ]);
    }

    /**
     * Saldo acumulado HASTA (incluyendo) un año, para usarlo como "saldo anterior"
     * en el reporte de Estado de Cuenta (reemplaza bo_fin_caja::EstadoCuentaSaldo()).
     *
     * OJO — replica una inconsistencia real del legacy, no un descuido nuestro:
     * a diferencia de movimientos() y de saldo(), esta consulta NO filtra
     * ct_vigencia='1'. Es decir, el "saldo anterior" que se suma en el reporte
     * SÍ incluye movimientos anulados de años previos, mientras que el detalle
     * del año actual (movimientos()) los excluye. Verificado con datos reales:
     * hay 259 movimientos anulados en el dump, por lo que esto puede producir
     * un "saldo final" distinto al de saldo() para socios con anulaciones en
     * años anteriores. Se mantiene así para que el reporte dé el mismo número
     * que el sistema legacy (Fase 10: mismo resultado, no una versión "corregida"
     * silenciosa).
     */
    public function saldoAcumuladoHasta(string $ccPersona, string $anho): string
    {
        return (string) FinEstadoCuenta::query()
            ->where('cc_persona', $ccPersona)
            ->whereYear('ct_fecha', '<=', $anho)
            ->sum('ct_monto');
    }

    /**
     * Resumen del estado de cuenta de TODOS los socios, una fila por socio.
     * Aplica exactamente las mismas reglas que el reporte individual
     * (movimientos() + saldoAcumuladoHasta()), pero agregadas en SQL con GROUP BY
     * para no hacer una consulta por socio:
     *   - débito/crédito: solo movimientos vigentes del año/concepto filtrado.
     *   - saldo anterior: acumulado hasta el año previo SIN filtrar vigencia ni
     *     concepto (misma inconsistencia legacy documentada arriba).
     * Incluye a todo socio con movimientos en el periodo o con saldo anterior
     * distinto de cero.
     *
     * @return Collection<int, array{cc_persona: int, ct_nro_doc: string, ct_nombres: string, debito: float, credito: float, saldo_anterior: float, saldo_final: float}>
     */
    public function resumenTodosLosSocios(?string $anho = null, ?string $ccConcepto = null): Collection
    {
        $periodo = FinEstadoCuenta::query()
            ->vigente()
            ->when($anho, fn ($q) => $q->whereYear('ct_fecha', $anho))
            ->when($ccConcepto, fn ($q) => $q->where('cc_concepto', $ccConcepto))
            ->groupBy('cc_persona')
            ->selectRaw('cc_persona, SUM(CASE WHEN ct_monto < 0 THEN ct_monto ELSE 0 END) AS debito, SUM(CASE WHEN ct_monto > 0 THEN ct_monto ELSE 0 END) AS credito')
            ->toBase()
            ->get()
            ->keyBy(fn ($fila) => (int) $fila->cc_persona);

        $saldosAnteriores = $anho
            ? FinEstadoCuenta::query()
                ->whereYear('ct_fecha', '<=', (string) ((int) $anho - 1))
                ->groupBy('cc_persona')
                ->selectRaw('cc_persona, SUM(ct_monto) AS saldo')
                ->toBase()
                ->get()
                ->keyBy(fn ($fila) => (int) $fila->cc_persona)
                ->filter(fn ($fila) => (float) $fila->saldo != 0.0)
            : collect();

        $idsSocios = $periodo->keys()->merge($saldosAnteriores->keys())->unique();

        return GenPersona::whereIn('cc_persona', $idsSocios->all())
            ->orderBy('ct_nombres')
            ->get()
            ->map(function (GenPersona $persona) use ($periodo, $saldosAnteriores) {
                $debito = (float) ($periodo->get($persona->cc_persona)?->debito ?? 0);
                $credito = (float) ($periodo->get($persona->cc_persona)?->credito ?? 0);
                $saldoAnterior = (float) ($saldosAnteriores->get($persona->cc_persona)?->saldo ?? 0);

                return [
                    'cc_persona' => $persona->cc_persona,
                    'ct_nro_doc' => (string) $persona->ct_nro_doc,
                    'ct_nombres' => (string) $persona->ct_nombres,
                    'debito' => $debito,
                    'credito' => $credito,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_final' => round($debito + $credito + $saldoAnterior, 2),
                ];
            })
            ->values();
    }
}
