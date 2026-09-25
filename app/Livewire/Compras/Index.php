<?php

namespace App\Livewire\Compras;

use App\Exports\ResumenComprasExport;
use App\Models\Legacy\FinCompras;
use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\FinSubconcepto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reemplaza compras/com_index.php + com_from.php + com_update.php.
 * Reglas replicadas de bo_fin_compras.php / dao_fin_compras.php:
 *  - cc_concepto solo lista conceptos de tipo Egreso ('E') vigentes (Selectlistar('E'))
 *  - ct_fecha (registro) siempre es "ahora"; ct_fecha_doc es la fecha del comprobante,
 *    y son campos distintos e independientes, igual que en el legacy
 *  - emp_ruc/emp_razon_social se escriben a mano, sin exigir que exista en Proveedores
 *    (igual que el legacy: no hay FK real a gen_empresa)
 */
class Index extends Component
{
    use WithPagination;

    public bool $mostrandoFormulario = false;
    public ?int $cc_compras = null;

    public string $emp_ruc = '';
    public string $emp_razon_social = '';
    public string $ct_comprobante = '';
    public string $ct_serie = '';
    public string $ct_numero = '';
    public string $cc_concepto = '';
    public string $cc_subconcepto = '';
    public string $ct_glosa = '';
    public string $ct_monto = '';
    public string $ct_fecha_doc = '';
    public string $ct_observacion = '';

    // Resumen por concepto (reemplaza reportes/egr_index.php + egr_listar.php, sin detalle)
    public bool $mostrandoResumen = false;
    public string $resumenAnho = '';
    public string $resumenPeriodo = '';
    public array $resumenFilas = [];

    protected array $reglas = [
        'emp_ruc' => ['required', 'digits:11'],
        'emp_razon_social' => ['required', 'string', 'max:150'],
        'ct_comprobante' => ['required', 'in:RI,BL,FC,LQ,TK'],
        'cc_concepto' => ['required'],
        'ct_glosa' => ['required', 'string', 'max:150'],
        'ct_monto' => ['required', 'numeric', 'min:0.01'],
        'ct_fecha_doc' => ['nullable', 'date'],
    ];

    public function nuevo(): void
    {
        $this->reset([
            'cc_compras', 'emp_ruc', 'emp_razon_social', 'ct_comprobante', 'ct_serie', 'ct_numero',
            'cc_concepto', 'cc_subconcepto', 'ct_glosa', 'ct_monto', 'ct_fecha_doc', 'ct_observacion',
        ]);
        $this->mostrandoFormulario = true;
    }

    public function editar(int $ccCompras): void
    {
        $compra = FinCompras::findOrFail($ccCompras);

        $this->cc_compras = $compra->cc_compras;
        $this->emp_ruc = (string) $compra->emp_ruc;
        $this->emp_razon_social = (string) $compra->emp_razon_social;
        $this->ct_comprobante = (string) $compra->ct_comprobante;
        $this->ct_serie = (string) $compra->ct_serie;
        $this->ct_numero = (string) $compra->ct_numero;
        $this->cc_concepto = (string) $compra->cc_concepto;
        $this->cc_subconcepto = (string) $compra->cc_subconcepto;
        $this->ct_glosa = (string) $compra->ct_glosa;
        $this->ct_monto = (string) $compra->ct_monto;
        $this->ct_fecha_doc = $compra->ct_fecha_doc ? substr((string) $compra->ct_fecha_doc, 0, 10) : '';
        $this->ct_observacion = (string) $compra->ct_observacion;

        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate($this->reglas);

        $datos = [
            'emp_ruc' => $this->emp_ruc,
            'emp_razon_social' => $this->emp_razon_social,
            'ct_comprobante' => $this->ct_comprobante,
            'ct_serie' => $this->ct_serie ?: null,
            'ct_numero' => $this->ct_numero ?: null,
            'cc_concepto' => $this->cc_concepto,
            'cc_subconcepto' => $this->cc_subconcepto ?: null,
            'ct_glosa' => $this->ct_glosa,
            'ct_monto' => $this->ct_monto,
            'ct_fecha_doc' => $this->ct_fecha_doc ?: null,
            'ct_observacion' => $this->ct_observacion ?: null,
            'cc_usuario' => Auth::id(),
        ];

        if ($this->cc_compras) {
            FinCompras::where('cc_compras', $this->cc_compras)->update($datos);
        } else {
            $datos['ct_fecha'] = now();
            $datos['ct_vigencia'] = '1';
            FinCompras::create($datos);
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Egreso guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    public function alternarResumen(): void
    {
        $this->mostrandoResumen = ! $this->mostrandoResumen;
    }

    /**
     * Replica bo_fin_compras::resumenCompras(): agrupa por concepto, filtrando
     * por ct_fecha_doc (no ct_fecha) exacto por año/mes, no acumulado.
     */
    public function buscarResumen(): void
    {
        $this->resumenFilas = DB::connection('legacy')->table('fin_compras as c')
            ->join('fin_concepto as ct', 'c.cc_concepto', '=', 'ct.cc_concepto')
            ->select('c.cc_concepto', 'ct.ct_nombre')
            ->selectRaw('sum(c.ct_monto) as monto')
            ->when($this->resumenAnho, fn ($q) => $q->whereYear('c.ct_fecha_doc', $this->resumenAnho))
            ->when($this->resumenPeriodo, fn ($q) => $q->whereMonth('c.ct_fecha_doc', $this->resumenPeriodo))
            ->groupBy('c.cc_concepto', 'ct.ct_nombre')
            ->get()
            ->all();
    }

    public function exportarResumenExcel()
    {
        return Excel::download(new ResumenComprasExport(collect($this->resumenFilas)), 'resumen-egresos.xlsx');
    }

    public function subconceptosDe(?string $ccConcepto)
    {
        if (! $ccConcepto) {
            return collect();
        }

        return FinSubconcepto::where('cc_concepto', $ccConcepto)->where('ct_vigencia', '1')->get();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $compras = FinCompras::query()
            ->orderByDesc('ct_fecha_doc')
            ->paginate(15);

        $conceptos = FinConcepto::where('ct_tipo', 'E')->where('ct_vigencia', '1')->orderBy('ct_nombre')->get();

        return view('livewire.compras.index', [
            'compras' => $compras,
            'conceptos' => $conceptos,
            'subconceptos' => $this->subconceptosDe($this->cc_concepto),
            'comprobantes' => ['RI' => 'Recibo interno', 'BL' => 'Boleta', 'FC' => 'Factura', 'LQ' => 'Liquidación', 'TK' => 'Ticket'],
        ]);
    }
}
