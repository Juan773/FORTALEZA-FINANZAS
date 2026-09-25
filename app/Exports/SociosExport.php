<?php

namespace App\Exports;

use App\Models\Legacy\GenPersona;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SociosExport implements FromCollection, WithHeadings
{
    /** @param  Collection<int, GenPersona>  $socios */
    public function __construct(private readonly Collection $socios) {}

    public function collection(): Collection
    {
        return $this->socios->map(fn (GenPersona $socio) => [
            $socio->codigoFormateado(),
            $socio->ct_nombres,
            $socio->ct_nro_doc,
            $socio->cp_sexo,
            $socio->ct_celular,
            $socio->ct_email,
            $socio->ct_zona,
            $socio->cfl_vigencia == 1 ? 'Vigente' : 'Inactivo',
        ]);
    }

    public function headings(): array
    {
        return ['Código', 'Nombres', 'Documento', 'Sexo', 'Celular', 'Email', 'Zona', 'Estado'];
    }
}
