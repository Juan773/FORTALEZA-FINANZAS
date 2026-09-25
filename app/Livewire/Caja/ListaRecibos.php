<?php

namespace App\Livewire\Caja;

use App\Models\Legacy\FinCaja;
use App\Services\CajaService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Reemplaza caja/rcb_index.php + rcb_lista.php + la anulación (bo_fin_caja::anular_recibo).
 */
class ListaRecibos extends Component
{
    use WithPagination;

    public string $anho;

    public function mount(): void
    {
        $this->anho = (string) now()->year;
    }

    public function anular(int $ccCaja, CajaService $cajaService): void
    {
        $cajaService->anularRecibo($ccCaja);
        session()->flash('status', 'Recibo anulado correctamente.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $recibos = FinCaja::query()
            ->with('persona', 'detalle')
            ->whereYear('caj_fecha', $this->anho)
            ->orderByDesc('caj_fecha')
            ->paginate(15);

        return view('livewire.caja.lista-recibos', ['recibos' => $recibos]);
    }
}
