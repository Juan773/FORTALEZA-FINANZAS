<?php

namespace App\Exports;

use App\Models\Legacy\GenPersona;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Reporte general de socios: TODAS las columnas de gen_personas, tal como están
 * almacenadas (sin decodificar códigos de parámetro ni renombrar a etiquetas de
 * negocio), para poder auditar/revisar los datos crudos de la base de datos.
 */
class SociosExport implements FromCollection, WithHeadings
{
    /** @param  Collection<int, GenPersona>  $socios */
    public function __construct(private readonly Collection $socios) {}

    public function collection(): Collection
    {
        return $this->socios->map(fn (GenPersona $socio) => [
            $socio->codigoFormateado(),
            $socio->ct_fec_reg,
            $socio->ct_tp_user,
            $socio->ct_nombres,
            $socio->ct_conyugue,
            $socio->ct_conyuguen,
            $socio->ct_hijos,
            $socio->ct_fech_nac,
            $socio->ct_religion,
            $socio->cp_tipo_doc,
            $socio->cc_profesion,
            $socio->ct_est_civil,
            $socio->ct_dpto,
            $socio->ct_zona,
            $socio->ct_manzana,
            $socio->ct_pago,
            $socio->ct_nro_doc,
            $socio->cp_sexo,
            $socio->ct_email,
            $socio->ct_celular,
            $socio->ct_direccion,
            $socio->cfl_vigencia,
            $socio->ct_obs,
        ]);
    }

    public function headings(): array
    {
        return [
            'cc_persona', 'ct_fec_reg', 'ct_tp_user', 'ct_nombres', 'ct_conyugue',
            'ct_conyuguen', 'ct_hijos', 'ct_fech_nac', 'ct_religion', 'cp_tipo_doc',
            'cc_profesion', 'ct_est_civil', 'ct_dpto', 'ct_zona', 'ct_manzana',
            'ct_pago', 'ct_nro_doc', 'cp_sexo', 'ct_email', 'ct_celular',
            'ct_direccion', 'cfl_vigencia', 'ct_obs',
        ];
    }
}
