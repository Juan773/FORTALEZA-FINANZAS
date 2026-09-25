<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Legacy\GenPersona;
use App\Services\EstadoCuentaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Reemplaza reportes/est_ficha.php + su impresión vía window.print().
 * Recalcula exactamente lo mismo que ve la pantalla (EstadoCuentaIndex),
 * con los mismos parámetros de búsqueda pasados por query string.
 */
class EstadoCuentaPdfController extends Controller
{
    public function __invoke(Request $request, EstadoCuentaService $estadoCuenta): Response
    {
        $anho = $request->query('anho', (string) now()->year);
        $ctNroDoc = $request->query('ct_nro_doc');
        $ccConcepto = $request->query('cc_concepto') ?: null;

        $persona = GenPersona::where('ct_nro_doc', $ctNroDoc)->firstOrFail();

        $movimientos = $estadoCuenta->movimientos($persona->cc_persona, $anho, $ccConcepto)->all();
        $totalDebito = (string) collect($movimientos)->sum('ingreso');
        $totalCredito = (string) collect($movimientos)->sum('egreso');
        $saldoAnterior = $estadoCuenta->saldoAcumuladoHasta($persona->cc_persona, (string) ((int) $anho - 1));
        $saldoFinal = (string) ((float) $totalDebito + (float) $totalCredito + (float) $saldoAnterior);

        return Pdf::loadView('pdf.estado-cuenta', [
            'anho' => $anho,
            'socio' => ['ct_nombres' => $persona->ct_nombres, 'ct_nro_doc' => $persona->ct_nro_doc],
            'movimientos' => $movimientos,
            'totalDebito' => $totalDebito,
            'totalCredito' => $totalCredito,
            'saldoAnterior' => $saldoAnterior,
            'saldoFinal' => $saldoFinal,
        ])->stream("estado-cuenta-{$persona->ct_nro_doc}-{$anho}.pdf");
    }
}
