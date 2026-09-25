<?php

namespace App\Livewire\Perfiles;

use App\Models\Legacy\SegPerfil;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza seguridad/per_index.php + per_lista.php + per_form.php + per_update.php.
 * No incluye cc_modulo_defecto: el sistema nuevo no tiene el menú dinámico legacy
 * basado en seg_modulo (ver nota en el modelo SegPerfil).
 */
class Index extends Component
{
    public bool $mostrandoFormulario = false;
    public ?int $cc_perfil = null;
    public string $ct_perfil = '';
    public bool $cfl_vigencia = true;

    public function nuevo(): void
    {
        $this->reset(['cc_perfil', 'ct_perfil']);
        $this->cfl_vigencia = true;
        $this->mostrandoFormulario = true;
    }

    public function editar(int $ccPerfil): void
    {
        $perfil = SegPerfil::findOrFail($ccPerfil);

        $this->cc_perfil = $perfil->cc_perfil;
        $this->ct_perfil = $perfil->ct_perfil;
        $this->cfl_vigencia = $perfil->cfl_vigencia == 1;
        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate(['ct_perfil' => ['required', 'string', 'max:50']]);

        if ($this->cc_perfil) {
            SegPerfil::where('cc_perfil', $this->cc_perfil)->update([
                'ct_perfil' => $this->ct_perfil,
                'cfl_vigencia' => $this->cfl_vigencia ? '1' : '0',
            ]);
        } else {
            SegPerfil::create([
                'cc_perfil' => SegPerfil::siguienteId(),
                'ct_perfil' => $this->ct_perfil,
                'cfl_vigencia' => '1',
            ]);
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Perfil guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.perfiles.index', ['perfiles' => SegPerfil::orderBy('ct_perfil')->get()]);
    }
}
