<?php

namespace App\Livewire\EstadoCuenta;

use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\GenPersona;
use App\Services\EstadoCuentaService;
use App\Support\NumeroALetras;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza reportes/est_index.php + est_baseindex.php + est_ficha.php.
 * Ver App\Services\EstadoCuentaService por la fórmula exacta de saldo anterior
 * (replica una inconsistencia real del legacy respecto a movimientos anulados).
 */
class Index extends Component
{
    public string $anho;
    public string $ct_nro_doc = '';
    public string $cc_concepto = '';

    public ?array $socio = null;
    public array $movimientos = [];
    public string $saldoAnterior = '0';
    public string $saldoFinal = '0';
    public string $totalDebito = '0';
    public string $totalCredito = '0';
    public bool $buscado = false;

    public function mount(): void
    {
        $this->anho = (string) now()->year;
    }

    public function buscar(EstadoCuentaService $estadoCuenta): void
    {
        $this->buscado = true;
        $this->socio = null;

        $persona = GenPersona::where('ct_nro_doc', $this->ct_nro_doc)->first();

        if (! $persona) {
            $this->addError('ct_nro_doc', 'No se encontró un socio con ese documento.');

            return;
        }

        $this->socio = ['cc_persona' => $persona->cc_persona, 'ct_nombres' => $persona->ct_nombres, 'ct_nro_doc' => $persona->ct_nro_doc];

        $this->movimientos = $estadoCuenta->movimientos($persona->cc_persona, $this->anho, $this->cc_concepto ?: null)->all();

        $this->totalDebito = (string) collect($this->movimientos)->sum('ingreso');
        $this->totalCredito = (string) collect($this->movimientos)->sum('egreso');

        $this->saldoAnterior = $estadoCuenta->saldoAcumuladoHasta($persona->cc_persona, (string) ((int) $this->anho - 1));
        $this->saldoFinal = (string) ((float) $this->totalDebito + (float) $this->totalCredito + (float) $this->saldoAnterior);
    }

    public function deudaEnLetras(): string
    {
        return (float) $this->saldoFinal > 0
            ? NumeroALetras::convertir($this->saldoFinal).' soles'
            : '0.00 soles';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.estado-cuenta.index', [
            'anhos' => range((int) now()->year, (int) now()->year - 9),
            'conceptos' => FinConcepto::where('ct_tipo', 'I')->where('ct_vigencia', '1')->orderBy('ct_nombre')->get(),
        ]);
    }
}
