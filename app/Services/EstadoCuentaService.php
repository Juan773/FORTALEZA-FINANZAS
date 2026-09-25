<?php

namespace App\Services;

use App\Models\Legacy\FinEstadoCuenta;
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
}
