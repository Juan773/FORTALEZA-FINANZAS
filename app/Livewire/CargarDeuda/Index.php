<?php

namespace App\Livewire\CargarDeuda;

use App\Models\Auditoria;
use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\FinEstadoCuenta;
use App\Models\Legacy\GenPersona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza datos_generales/deu_form.php + deu_update.php: carga un cargo (tipo 'D')
 * a un socio puntual, o a todos los socios activos a la vez (checkbox "todos" del legacy).
 */
class Index extends Component
{
    public string $ct_fecha;
    public string $cc_persona = '';
    public bool $todos = false;
    public string $cc_concepto = '';
    public string $cc_descripcion = '';
    public string $ct_monto = '';

    public function mount(): void
    {
        $this->ct_fecha = now()->format('Y-m-d');
    }

    public function guardar(): void
    {
        $this->validate([
            'ct_fecha' => ['required', 'date'],
            'cc_persona' => [$this->todos ? 'nullable' : 'required'],
            'cc_concepto' => ['required'],
            'cc_descripcion' => ['required', 'string', 'max:120'],
            'ct_monto' => ['required', 'numeric', 'min:0.01'],
        ]);

        $destinatarios = $this->todos
            ? GenPersona::where('cfl_vigencia', '1')->pluck('cc_persona')
            : collect([$this->cc_persona]);

        DB::connection('legacy')->transaction(function () use ($destinatarios) {
            foreach ($destinatarios as $ccPersona) {
                FinEstadoCuenta::create([
                    'cc_caja' => 0,
                    'cc_persona' => $ccPersona,
                    'ct_tipo' => 'D',
                    'ct_fecha' => $this->ct_fecha,
                    'cc_concepto' => $this->cc_concepto,
                    'cc_descripcion' => $this->cc_descripcion,
                    'ct_monto' => $this->ct_monto,
                    'sys_user' => Auth::id(),
                    'ct_vigencia' => '1',
                ]);
            }
        });

        Auditoria::registrar('deuda.cargar', $this->todos
            ? "Cargó una deuda de {$this->ct_monto} a {$destinatarios->count()} socios activos"
            : "Cargó una deuda de {$this->ct_monto} al socio {$this->cc_persona}", [
                'todos' => $this->todos,
                'cc_concepto' => $this->cc_concepto,
                'ct_monto' => $this->ct_monto,
                'cantidad_socios' => $destinatarios->count(),
            ]);

        session()->flash('status', $this->todos
            ? 'Deuda cargada a '.$destinatarios->count().' socios activos.'
            : 'Deuda cargada correctamente.');

        $this->reset(['cc_persona', 'todos', 'cc_concepto', 'cc_descripcion', 'ct_monto']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.cargar-deuda.index', [
            'socios' => GenPersona::where('cfl_vigencia', '1')->orderBy('ct_nombres')->get(),
            'conceptos' => FinConcepto::where('ct_tipo', 'I')->where('ct_vigencia', '1')->orderBy('ct_nombre')->get(),
        ]);
    }
}
