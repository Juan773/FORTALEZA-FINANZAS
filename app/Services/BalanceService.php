<?php

namespace App\Services;

use App\Models\Legacy\FinEstadoCuenta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Replica bo_fin_caja.php: balanceAnhoMes() y valanceTodos().
 * Ninguno de los dos filtra ct_vigencia='1' (a diferencia de EstadoCuentaService::saldo()):
 * ambos incluyen movimientos anulados. Se preserva así porque es lo que el sistema
 * legacy muestra hoy en las pantallas de Balance (reportes/bal_listar.php y
 * reportes/asob_lista.php) — otra inconsistencia real documentada, no un descuido.
 */
class BalanceService
{
    /**
     * Balance por concepto. Replica una particularidad real del legacy: el año
     * filtra "acumulado hasta" (<=), pero el mes (si se indica) filtra EXACTO,
     * no dentro del año seleccionado. Es decir, año=2020 + mes=05 suma TODOS los
     * mayos de cualquier año hasta 2020 inclusive, no solo mayo de 2020. Se
     * mantiene tal cual el original (bo_fin_caja::balanceAnhoMes).
     */
    public function porConcepto(string $anho, ?string $periodo = null): Collection
    {
        return FinEstadoCuenta::query()
            ->select('cc_concepto')
            ->selectRaw("SUM(CASE WHEN ct_tipo='D' THEN ct_monto ELSE 0 END) as debe")
            ->selectRaw("SUM(CASE WHEN ct_tipo='P' THEN ct_monto ELSE 0 END) as haber")
            ->with('concepto')
            ->whereYear('ct_fecha', '<=', $anho)
            ->when($periodo, fn ($q) => $q->whereMonth('ct_fecha', $periodo))
            ->groupBy('cc_concepto')
            ->get()
            ->map(fn ($fila) => [
                'cc_concepto' => $fila->cc_concepto,
                'ct_nombre' => $fila->concepto?->ct_nombre ?? '',
                'debe' => $fila->debe,
                'haber' => $fila->haber,
            ]);
    }

    /**
     * Balance consolidado por socio (debe/haber/saldo de todo el histórico).
     * Agrupa por (nombre, documento) igual que el legacy, no por cc_persona:
     * en datos reales con nombres/documentos en blanco esto puede fusionar
     * filas de personas distintas — se preserva por fidelidad, no por diseño.
     *
     * $cflVigencia acepta '1' (activo) o '0' (inactivo). El dropdown legacy
     * ofrecía '2' para "Inactivo", que nunca calzaba con ningún dato real
     * (cfl_vigencia solo vale 0 o 1) y por lo tanto el filtro estaba roto en
     * la práctica; acá se corrige a '0' porque no es una regla de negocio
     * real, es un typo en el value de un <option> que nunca sirvió para nada.
     */
    public function porSocio(?string $ccConcepto = null, ?string $cflVigencia = null): Collection
    {
        return DB::connection('legacy')->table('fin_estado_cuenta as e')
            ->join('gen_personas as p', 'p.cc_persona', '=', 'e.cc_persona')
            ->select('p.ct_nombres', 'p.ct_nro_doc')
            ->selectRaw("SUM(CASE WHEN e.ct_tipo='D' THEN e.ct_monto ELSE 0 END) as debe")
            ->selectRaw("SUM(CASE WHEN e.ct_tipo='P' THEN e.ct_monto ELSE 0 END) as haber")
            ->selectRaw("SUM(CASE WHEN e.ct_tipo='D' THEN e.ct_monto ELSE 0 END) + SUM(CASE WHEN e.ct_tipo='P' THEN e.ct_monto ELSE 0 END) as saldo")
            ->when($ccConcepto, fn ($q) => $q->where('e.cc_concepto', $ccConcepto))
            ->when($cflVigencia !== null && $cflVigencia !== '', fn ($q) => $q->where('p.cfl_vigencia', $cflVigencia))
            ->groupBy('p.ct_nombres', 'p.ct_nro_doc')
            ->get();
    }
}
