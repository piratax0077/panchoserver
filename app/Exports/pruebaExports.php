<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\InvoicesPerMonthSheet;

class pruebaExports implements WithMultipleSheets
{
    use Exportable;

    protected $fecha;
    
    public function __construct($fecha)
    {
        $this->fecha = $fecha;
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new InvoicesPerMonthSheet($this->fecha);
        return $sheets;
    }
}
