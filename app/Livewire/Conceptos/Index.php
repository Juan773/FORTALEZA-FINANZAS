<?php

namespace App\Livewire\Conceptos;

use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\FinSubconcepto;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza datos_generales/con_index.php + con_form.php + con_update.php
 * y, para los subconceptos, sub_form.php + sub_update.php.
 */
class Index extends Component
{
    public bool $mostrandoFormulario = false;
    public ?int $cc_concepto = null;
    public string $ct_tipo = 'I';
    public string $ct_nombre = '';
    public bool $ct_vigencia = true;

    public ?int $conceptoExpandido = null;
    public string $nuevoSubconceptoNombre = '';

    protected array $reglas = [
        'ct_tipo' => ['required', 'in:I,E'],
        'ct_nombre' => ['required', 'string', 'max:120'],
    ];

    public function nuevo(): void
    {
        $this->reset(['cc_concepto', 'ct_nombre']);
        $this->ct_tipo = 'I';
        $this->ct_vigencia = true;
        $this->mostrandoFormulario = true;
    }

    public function editar(int $ccConcepto): void
    {
        $concepto = FinConcepto::findOrFail($ccConcepto);

        $this->cc_concepto = $concepto->cc_concepto;
        $this->ct_tipo = $concepto->ct_tipo;
        $this->ct_nombre = $concepto->ct_nombre;
        $this->ct_vigencia = $concepto->ct_vigencia == '1';
        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate($this->reglas);

        $datos = [
            'ct_tipo' => $this->ct_tipo,
            'ct_nombre' => $this->ct_nombre,
            'ct_vigencia' => $this->ct_vigencia ? '1' : '0',
        ];

        if ($this->cc_concepto) {
            FinConcepto::where('cc_concepto', $this->cc_concepto)->update($datos);
        } else {
            $datos['ct_vigencia'] = '1';
            FinConcepto::create($datos);
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Concepto guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    public function alternarSubconceptos(int $ccConcepto): void
    {
        $this->conceptoExpandido = $this->conceptoExpandido === $ccConcepto ? null : $ccConcepto;
        $this->nuevoSubconceptoNombre = '';
    }

    public function agregarSubconcepto(int $ccConcepto): void
    {
        $this->validate(['nuevoSubconceptoNombre' => ['required', 'string', 'max:50']]);

        FinSubconcepto::create([
            'cc_concepto' => $ccConcepto,
            'ct_nombre' => $this->nuevoSubconceptoNombre,
            'ct_vigencia' => '1',
        ]);

        $this->nuevoSubconceptoNombre = '';
    }

    public function quitarSubconcepto(int $ccSubconcepto): void
    {
        FinSubconcepto::where('cc_subconcepto', $ccSubconcepto)->update(['ct_vigencia' => '0']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $conceptos = FinConcepto::with('subconceptos')->orderBy('ct_tipo')->orderBy('ct_nombre')->get();

        return view('livewire.conceptos.index', ['conceptos' => $conceptos]);
    }
}
