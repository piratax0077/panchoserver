<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ventasDiariasExportsGetnet implements WithMultipleSheets
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
        $sheets[] = new BoletasPorDiaSheetGetnet($this->fecha);
        $sheets[] = new FacturasPorDiaSheetGetnet($this->fecha);
        return $sheets;
    }
}
