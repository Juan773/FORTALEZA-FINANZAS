<?php

namespace App\Livewire\Proveedores;

use App\Models\Legacy\GenEmpresa;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza datos_generales/emp_index.php + emp_form.php + emp_update.php.
 * El legacy solo validaba RUC duplicado al insertar; acá también se excluye
 * el propio registro al editar (evita dejar dos proveedores con el mismo RUC).
 */
class Index extends Component
{
    public bool $mostrandoFormulario = false;
    public ?int $emp_id = null;

    public string $emp_razon_social = '';
    public string $emp_nom_comercial = '';
    public string $emp_ruc = '';
    public string $emp_direccion = '';
    public string $emp_telefono = '';
    public string $emp_celular = '';
    public string $emp_email = '';
    public string $emp_web = '';
    public bool $emp_estado = true;

    protected array $reglas = [
        'emp_razon_social' => ['required', 'string', 'max:200'],
        'emp_nom_comercial' => ['nullable', 'string', 'max:200'],
        'emp_ruc' => ['required', 'digits:11'],
        'emp_direccion' => ['nullable', 'string', 'max:150'],
        'emp_telefono' => ['nullable', 'string', 'max:15'],
        'emp_celular' => ['nullable', 'string', 'max:15'],
        'emp_email' => ['nullable', 'email', 'max:45'],
        'emp_web' => ['nullable', 'string', 'max:120'],
    ];

    public function nuevo(): void
    {
        $this->reset([
            'emp_id', 'emp_razon_social', 'emp_nom_comercial', 'emp_ruc',
            'emp_direccion', 'emp_telefono', 'emp_celular', 'emp_email', 'emp_web',
        ]);
        $this->emp_estado = true;
        $this->mostrandoFormulario = true;
    }

    public function editar(int $empId): void
    {
        $proveedor = GenEmpresa::findOrFail($empId);

        $this->emp_id = $proveedor->emp_id;
        $this->emp_razon_social = (string) $proveedor->emp_razon_social;
        $this->emp_nom_comercial = (string) $proveedor->emp_nom_comercial;
        $this->emp_ruc = (string) $proveedor->emp_ruc;
        $this->emp_direccion = (string) $proveedor->emp_direccion;
        $this->emp_telefono = (string) $proveedor->emp_telefono;
        $this->emp_celular = (string) $proveedor->emp_celular;
        $this->emp_email = (string) $proveedor->emp_email;
        $this->emp_web = (string) $proveedor->emp_web;
        $this->emp_estado = $proveedor->emp_estado === '1';

        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate($this->reglas);

        $rucDuplicado = GenEmpresa::query()
            ->where('emp_ruc', $this->emp_ruc)
            ->when($this->emp_id, fn ($q) => $q->where('emp_id', '<>', $this->emp_id))
            ->exists();

        if ($rucDuplicado) {
            $this->addError('emp_ruc', 'Ya existe un proveedor con ese RUC.');

            return;
        }

        $datos = [
            'emp_razon_social' => $this->emp_razon_social,
            'emp_nom_comercial' => $this->emp_nom_comercial ?: null,
            'emp_ruc' => $this->emp_ruc,
            'emp_direccion' => $this->emp_direccion ?: null,
            'emp_telefono' => $this->emp_telefono ?: null,
            'emp_celular' => $this->emp_celular ?: null,
            'emp_email' => $this->emp_email ?: null,
            'emp_web' => $this->emp_web ?: null,
            'emp_estado' => $this->emp_estado ? '1' : '0',
        ];

        if ($this->emp_id) {
            GenEmpresa::where('emp_id', $this->emp_id)->update($datos);
        } else {
            GenEmpresa::create($datos);
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Proveedor guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.proveedores.index', [
            'proveedores' => GenEmpresa::orderBy('emp_razon_social')->get(),
        ]);
    }
}
