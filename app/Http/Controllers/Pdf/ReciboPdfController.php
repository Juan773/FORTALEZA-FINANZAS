<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Legacy\FinCaja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Reemplaza caja/ver_recibo.php + ver_recibo_text.php + print.css + window.print():
 * un PDF real y descargable en vez de HTML impreso desde el navegador.
 */
class ReciboPdfController extends Controller
{
    public function __invoke(int $ccCaja): Response
    {
        $recibo = FinCaja::with(['persona', 'banco', 'cuenta', 'detalle.concepto'])->findOrFail($ccCaja);

        return Pdf::loadView('pdf.recibo', ['recibo' => $recibo])
            ->setPaper([0, 0, 400, 550])
            ->stream("recibo-{$recibo->voucher}.pdf");
    }
}
