<?php

namespace App\Livewire\Auditoria;

use App\Models\Auditoria;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $accion = '';

    #[Layout('layouts.app')]
    public function render()
    {
        $registros = Auditoria::query()
            ->when($this->accion, fn ($q) => $q->where('accion', $this->accion))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.auditoria.index', [
            'registros' => $registros,
            'acciones' => ['caja.emitir' => 'Emitir recibo', 'caja.anular' => 'Anular recibo', 'deuda.cargar' => 'Cargar deuda', 'usuario.crear' => 'Crear usuario', 'usuario.editar' => 'Editar usuario'],
        ]);
    }
}
