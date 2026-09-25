<?php

namespace App\Livewire\Balances;

use App\Exports\BalancePorConceptoExport;
use App\Exports\BalancePorSocioExport;
use App\Models\Legacy\FinConcepto;
use App\Services\BalanceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reemplaza reportes/bal_index.php + bal_listar.php (balance por concepto)
 * y reportes/asob_index.php + asob_lista.php (balance de asociados).
 * Ver App\Services\BalanceService por las fórmulas exactas replicadas.
 */
class Index extends Component
{
    public string $pestana = 'concepto';

    // Balance por concepto
    public string $anho;
    public string $periodo = '';

    // Balance por socio
    public string $cc_concepto = '';
    public string $cfl_vigencia = '';

    public array $filas = [];
    public bool $buscado = false;

    public function mount(): void
    {
        $this->anho = (string) now()->year;
    }

    public function cambiarPestana(string $pestana): void
    {
        $this->pestana = $pestana;
        $this->buscado = false;
        $this->filas = [];
    }

    public function buscarPorConcepto(BalanceService $balance): void
    {
        $this->filas = $balance->porConcepto($this->anho, $this->periodo ?: null)->all();
        $this->buscado = true;
    }

    public function buscarPorSocio(BalanceService $balance): void
    {
        $this->filas = $balance->porSocio($this->cc_concepto ?: null, $this->cfl_vigencia !== '' ? $this->cfl_vigencia : null)->all();
        $this->buscado = true;
    }

    public function exportarConceptoExcel()
    {
        return Excel::download(new BalancePorConceptoExport($this->filas), 'balance-por-concepto.xlsx');
    }

    public function exportarSocioExcel()
    {
        return Excel::download(new BalancePorSocioExport(collect($this->filas)), 'balance-por-socio.xlsx');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.balances.index', [
            'anhos' => range((int) now()->year, (int) now()->year - 9),
            'meses' => [
                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
            ],
            'conceptos' => FinConcepto::where('ct_tipo', 'I')->where('ct_vigencia', '1')->orderBy('ct_nombre')->get(),
        ]);
    }
}
