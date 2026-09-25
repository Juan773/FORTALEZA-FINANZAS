<?php

namespace App\Livewire\Socios;

use App\Exports\SociosExport;
use App\Models\Legacy\GenPersona;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reemplaza datos_generales/per_lista.php + per_form.php + per_update.php.
 * Reglas replicadas de bo_gen_personas.php:
 *  - nombres se guardan en mayúsculas
 *  - documento (tipo+número) único entre socios, excluyendo el propio registro al editar
 *  - alta nueva siempre queda vigente; al editar, vigente solo si el checkbox está marcado
 */
class Index extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroTipoUsuario = '';
    public string $filtroZona = '';
    public string $filtroVigencia = '';

    public bool $mostrandoFormulario = false;
    public ?string $cc_persona = null;

    public string $ct_nombres = '';
    public string $cp_tipo_doc = '1';
    public string $ct_nro_doc = '';
    public string $cp_sexo = 'M';
    public string $ct_email = '';
    public string $ct_celular = '';
    public string $ct_direccion = '';
    public string $ct_fech_nac = '';
    public string $ct_religion = '';
    public string $ct_est_civil = '';
    public string $ct_zona = '';
    public string $ct_tp_user = '02';
    public string $ct_conyugue = '';
    public string $ct_hijos = '0';
    public string $cc_profesion = '';
    public string $ct_manzana = '';
    public string $ct_pago = '';
    public string $ct_obs = '';
    public bool $cfl_vigencia = true;

    protected function reglas(): array
    {
        return [
            'ct_nombres' => ['required', 'string', 'max:150'],
            'cp_tipo_doc' => ['required', 'in:1,4'],
            'ct_nro_doc' => ['required', 'string', 'max:8'],
            'cp_sexo' => ['required', 'in:M,F'],
            'ct_email' => ['nullable', 'email', 'max:50'],
            'ct_celular' => ['nullable', 'string', 'max:50'],
            'ct_direccion' => ['nullable', 'string', 'max:250'],
            'ct_fech_nac' => ['nullable', 'date'],
            'ct_religion' => ['nullable', 'string'],
            'ct_est_civil' => ['nullable', 'string'],
            'ct_zona' => ['nullable', 'string'],
            'ct_tp_user' => ['required', 'in:01,02'],
            'ct_hijos' => ['nullable', 'string', 'max:2'],
        ];
    }

    public function catalogos(): array
    {
        return [
            'tipoDoc' => ['1' => 'DNI', '4' => 'Carnet de Extranjería'],
            'sexo' => ['M' => 'Masculino', 'F' => 'Femenino'],
            'religion' => ['1' => 'Adventista', '2' => 'Evangélico', '3' => 'Católico', '4' => 'Otros'],
            'estadoCivil' => ['01' => 'Soltero', '02' => 'Casado', '03' => 'Viuda(o)', '04' => 'Divorciado', '05' => 'Conviviente'],
            'zona' => ['01' => 'Costa', '02' => 'Selva', '03' => 'Yunga', '04' => 'Quechua', '05' => 'Suni'],
            'tipoUsuario' => ['01' => 'Administrativo', '02' => 'Socio'],
        ];
    }

    public function nuevo(): void
    {
        $this->reset([
            'cc_persona', 'ct_nombres', 'ct_nro_doc', 'ct_email', 'ct_celular', 'ct_direccion',
            'ct_fech_nac', 'ct_religion', 'ct_est_civil', 'ct_zona', 'ct_conyugue', 'cc_profesion',
            'ct_manzana', 'ct_pago', 'ct_obs',
        ]);
        $this->cp_tipo_doc = '1';
        $this->cp_sexo = 'M';
        $this->ct_tp_user = '02';
        $this->ct_hijos = '0';
        $this->cfl_vigencia = true;
        $this->mostrandoFormulario = true;
    }

    public function editar(string $ccPersona): void
    {
        $socio = GenPersona::findOrFail($ccPersona);

        $this->cc_persona = $socio->cc_persona;
        $this->ct_nombres = (string) $socio->ct_nombres;
        $this->cp_tipo_doc = (string) $socio->cp_tipo_doc;
        $this->ct_nro_doc = (string) $socio->ct_nro_doc;
        $this->cp_sexo = (string) $socio->cp_sexo;
        $this->ct_email = (string) $socio->ct_email;
        $this->ct_celular = (string) $socio->ct_celular;
        $this->ct_direccion = (string) $socio->ct_direccion;
        $this->ct_fech_nac = $socio->ct_fech_nac ? substr((string) $socio->ct_fech_nac, 0, 10) : '';
        $this->ct_religion = (string) $socio->ct_religion;
        $this->ct_est_civil = (string) $socio->ct_est_civil;
        $this->ct_zona = (string) $socio->ct_zona;
        $this->ct_tp_user = (string) ($socio->ct_tp_user ?: '02');
        $this->ct_conyugue = (string) $socio->ct_conyugue;
        $this->ct_hijos = (string) ($socio->ct_hijos ?: '0');
        $this->cc_profesion = (string) $socio->cc_profesion;
        $this->ct_manzana = (string) $socio->ct_manzana;
        $this->ct_pago = (string) $socio->ct_pago;
        $this->ct_obs = (string) $socio->ct_obs;
        $this->cfl_vigencia = $socio->cfl_vigencia == 1;

        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate($this->reglas());

        $documentoDuplicado = GenPersona::query()
            ->where('cp_tipo_doc', $this->cp_tipo_doc)
            ->where('ct_nro_doc', trim($this->ct_nro_doc))
            ->when($this->cc_persona, fn ($q) => $q->where('cc_persona', '<>', $this->cc_persona))
            ->exists();

        if ($documentoDuplicado) {
            $this->addError('ct_nro_doc', 'Ya existe un socio con ese tipo y número de documento.');

            return;
        }

        $datos = [
            'ct_nombres' => strtoupper($this->ct_nombres),
            'cp_tipo_doc' => $this->cp_tipo_doc,
            'ct_nro_doc' => trim($this->ct_nro_doc),
            'cp_sexo' => $this->cp_sexo,
            'ct_email' => $this->ct_email ?: null,
            'ct_celular' => $this->ct_celular ?: null,
            'ct_direccion' => $this->ct_direccion ?: null,
            'ct_fech_nac' => $this->ct_fech_nac ?: null,
            'ct_religion' => $this->ct_religion ?: null,
            'ct_est_civil' => $this->ct_est_civil ?: null,
            'ct_zona' => $this->ct_zona ?: null,
            'ct_tp_user' => $this->ct_tp_user,
            'ct_conyugue' => $this->ct_conyugue ?: null,
            'ct_conyuguen' => '',
            'ct_hijos' => $this->ct_hijos ?: '0',
            'cc_profesion' => $this->cc_profesion ?: null,
            'ct_manzana' => $this->ct_manzana ?: null,
            'ct_pago' => $this->ct_pago ?: null,
            'ct_obs' => $this->ct_obs ?: null,
        ];

        if ($this->cc_persona) {
            $datos['cfl_vigencia'] = $this->cfl_vigencia ? '1' : '0';
            GenPersona::where('cc_persona', $this->cc_persona)->update($datos);
        } else {
            DB::connection('legacy')->transaction(function () use ($datos) {
                $datos['cc_persona'] = GenPersona::siguienteId();
                $datos['cfl_vigencia'] = '1'; // alta nueva: siempre vigente, igual que el legacy.
                $datos['ct_fec_reg'] = now();
                GenPersona::create($datos);
            });
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Socio guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    /** Reemplaza bo_gen_personas::listarSocioReporte(): mismos tres filtros de reporte. */
    protected function consultaFiltrada()
    {
        return GenPersona::query()
            ->when($this->buscar, fn ($q) => $q->where('ct_nombres', 'like', '%'.$this->buscar.'%'))
            ->when($this->filtroTipoUsuario, fn ($q) => $q->where('ct_tp_user', $this->filtroTipoUsuario))
            ->when($this->filtroZona, fn ($q) => $q->where('ct_zona', $this->filtroZona))
            ->when($this->filtroVigencia !== '', fn ($q) => $q->where('cfl_vigencia', $this->filtroVigencia))
            ->orderBy('ct_nombres');
    }

    public function exportarExcel()
    {
        return Excel::download(new SociosExport($this->consultaFiltrada()->get()), 'reporte-socios.xlsx');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.socios.index', [
            'socios' => $this->consultaFiltrada()->paginate(15),
            'catalogos' => $this->catalogos(),
        ]);
    }
}
