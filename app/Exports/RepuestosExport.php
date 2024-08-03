<?php

namespace App\Exports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RepuestosExport implements FromQuery,WithHeadings,WithTitle,ShouldAutoSize,WithEvents
{
    use Exportable;
    protected $id_proveedor;
    
    public function __construct($id_proveedor)
    {
        $this->id_proveedor = $id_proveedor;
    }

    public function query()
    {
        try {
            return repuesto::query()->select('repuestos.codigo_interno','proveedores.empresa_nombre_corto','repuestos.cod_repuesto_proveedor')
            ->where('repuestos.id_proveedor',$this->id_proveedor)
            ->where('repuestos.activo',1)
            ->join('proveedores','repuestos.id_proveedor','proveedores.id');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:C1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(16);
                $event->sheet->getDelegate()->getStyle($cellRange)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('DD4B39');
                
            },
        ];
    }

    public function headings(): array
    {
        return ["Codigo interno","Proveedor","Cod Rep Prov"];
    }

    public function title(): string
    {
        return 'Repuestos_' . $this->id_proveedor;
    }

    
}
