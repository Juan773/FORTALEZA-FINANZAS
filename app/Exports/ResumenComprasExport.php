<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResumenComprasExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $filas) {}

    public function collection(): Collection
    {
        return $this->filas->map(fn ($fila) => [$fila->cc_concepto, $fila->ct_nombre, $fila->monto]);
    }

    public function headings(): array
    {
        return ['Código', 'Concepto', 'Monto'];
    }
}
