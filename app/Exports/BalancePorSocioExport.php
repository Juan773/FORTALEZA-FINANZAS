<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BalancePorSocioExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $filas) {}

    public function collection(): Collection
    {
        return $this->filas->map(fn ($fila) => [
            $fila->ct_nombres,
            $fila->ct_nro_doc,
            $fila->debe,
            $fila->haber,
            $fila->saldo,
        ]);
    }

    public function headings(): array
    {
        return ['Nombre', 'DNI', 'Debe', 'Haber', 'Saldo'];
    }
}
