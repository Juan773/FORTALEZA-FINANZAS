<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Legacy\FinCaja;
use App\Models\Legacy\FinCajaDetalle;
use App\Models\Legacy\FinComprobante;
use App\Models\Legacy\FinEstadoCuenta;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza caja/caj_update.php (bo_fin_caja + bo_fin_caja_detalle + bo_fin_estado_cuenta).
 *
 * A diferencia del legacy, NO replica crearClave() (función indefinida en el código
 * fuente que recibimos — decisión del cliente: no reconstruirla) ni el ActComprobante()
 * roto (nunca incrementaba nada porque usaba variables sin definir, ver hallazgo
 * documentado en la sesión de migración). En su lugar:
 *  - cc_caja / cc_caja_det / cc_estadocuenta: autoincrement real de MySQL (ya lo eran
 *    en el esquema, el legacy simplemente nunca lo usaba).
 *  - ct_numero (el correlativo visible del comprobante): se genera de forma atómica
 *    con un lock de fila sobre fin_comprobante dentro de una transacción, evitando
 *    que dos cajeros emitan el mismo número al mismo tiempo.
 */
class CajaService
{
    /**
     * @param  array<int, array{cc_concepto: string, ct_cantidad: float, ct_importe: float}>  $detalle
     */
    public function crearRecibo(array $cabecera, array $detalle): FinCaja
    {
        return DB::connection('legacy')->transaction(function () use ($cabecera, $detalle) {
            $comprobante = FinComprobante::where('ct_serie', $cabecera['ct_serie'])->lockForUpdate()->firstOrFail();
            $comprobante->ct_correlativo = (int) $comprobante->ct_correlativo + 1;
            $comprobante->save();

            $caja = FinCaja::create([
                'caj_ano' => $cabecera['caj_fecha']->format('Y'),
                'caj_fecha' => $cabecera['caj_fecha'],
                'cc_persona' => $cabecera['cc_persona'],
                'ct_serie' => $cabecera['ct_serie'],
                'ct_numero' => $comprobante->ct_correlativo,
                'pag_tipo' => $cabecera['pag_tipo'],
                'pag_fecha' => $cabecera['pag_fecha'] ?? null,
                'pag_nop' => $cabecera['pag_nop'] ?? null,
                'cc_banco' => $cabecera['cc_banco'] ?? null,
                'cc_cuenta' => $cabecera['cc_cuenta'] ?? null,
                'ct_vigencia' => '1',
                'caj_obs' => $cabecera['caj_obs'] ?? null,
                'cc_usuario' => $cabecera['cc_usuario'],
            ]);

            foreach ($detalle as $linea) {
                $total = round((float) $linea['ct_cantidad'] * (float) $linea['ct_importe'], 2);

                FinCajaDetalle::create([
                    'cc_caja' => $caja->cc_caja,
                    'cc_concepto' => $linea['cc_concepto'],
                    'ct_cantidad' => $linea['ct_cantidad'],
                    'ct_importe' => $linea['ct_importe'],
                    'ct_total' => $total,
                ]);

                FinEstadoCuenta::create([
                    'cc_caja' => $caja->cc_caja,
                    'cc_persona' => $cabecera['cc_persona'],
                    'ct_tipo' => 'P',
                    'ct_fecha' => $cabecera['caj_fecha'],
                    'cc_concepto' => $linea['cc_concepto'],
                    'cc_descripcion' => $cabecera['caj_obs'] ?? null,
                    'ct_monto' => -$total,
                    'sys_user' => $cabecera['cc_usuario'],
                    'ct_vigencia' => '1',
                ]);
            }

            Auditoria::registrar('caja.emitir', "Emitió el recibo {$caja->voucher}", [
                'cc_caja' => $caja->cc_caja,
                'cc_persona' => $cabecera['cc_persona'],
                'total' => collect($detalle)->sum(fn ($l) => round((float) $l['ct_cantidad'] * (float) $l['ct_importe'], 2)),
            ]);

            return $caja;
        });
    }

    /**
     * Replica exactamente dao_fin_caja::anular_recibo(): no solo marca vigencia='0',
     * también pone en cero cantidad/importe/total/monto (así quedan hoy los 259
     * movimientos anulados que ya existen en el dump — se preserva el mismo criterio).
     */
    public function anularRecibo(int $ccCaja): void
    {
        DB::connection('legacy')->transaction(function () use ($ccCaja) {
            $caja = FinCaja::with('detalle')->findOrFail($ccCaja);
            $totalAnulado = $caja->detalle->sum('ct_total');

            FinCaja::where('cc_caja', $ccCaja)->update(['ct_vigencia' => '0']);

            FinCajaDetalle::where('cc_caja', $ccCaja)->update([
                'ct_cantidad' => 0,
                'ct_importe' => 0,
                'ct_total' => 0,
            ]);

            FinEstadoCuenta::where('cc_caja', $ccCaja)->update([
                'ct_monto' => 0,
                'ct_vigencia' => '0',
            ]);

            Auditoria::registrar('caja.anular', "Anuló el recibo {$caja->voucher}", [
                'cc_caja' => $ccCaja,
                'monto_anulado' => (float) $totalAnulado,
            ]);
        });
    }
}
