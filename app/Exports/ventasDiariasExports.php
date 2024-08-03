<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\InvoicesPerMonthSheet;
use App\Exports\BoletasPorDiaSheet;
use App\Exports\FacturasPorDiaSheet;

class ventasDiariasExports implements WithMultipleSheets
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
        $sheets[] = new BoletasPorDiaSheet($this->fecha);
        $sheets[] = new FacturasPorDiaSheet($this->fecha);
        return $sheets;
    }
}
