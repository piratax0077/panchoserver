<?php

namespace App\Exports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Repuestos_actualizadosExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $repuestos;

    public function __construct($repuestos)
    {
        $this->repuestos = $repuestos;
    }

    public function collection()
    {
        return $this->repuestos;
    }

    public function headings(): array
    {
        return [
            'Codigo interno',
            'Descripción',
            'Stk. Bodega',
            'Stk. Tienda',
            'Stk. Casa Matríz',
            'Precio',
            // Agrega más columnas según tus necesidades
        ];
    }
}
