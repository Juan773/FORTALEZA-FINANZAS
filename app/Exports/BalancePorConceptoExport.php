<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BalancePorConceptoExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $filas) {}

    public function collection(): Collection
    {
        return collect($this->filas)->map(fn ($fila) => [
            $fila['cc_concepto'],
            $fila['ct_nombre'],
            $fila['debe'],
            $fila['haber'],
        ]);
    }

    public function headings(): array
    {
        return ['Código', 'Concepto', 'Debe', 'Haber'];
    }
}
