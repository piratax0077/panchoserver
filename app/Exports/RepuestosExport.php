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

class RepuestosExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
            return repuesto::query()
                ->select(
                    'repuestos.codigo_interno',
                    'proveedores.empresa_nombre_corto',
                    'repuestos.cod_repuesto_proveedor',
                    \DB::raw(\App\Services\StockPorLocalService::totalSql() . ' AS stock_total')
                )
                ->where('repuestos.id_proveedor', $this->id_proveedor)
                ->where('repuestos.activo', 1)
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id');
        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error en la exportación de repuestos', [
                'error' => $e->getMessage(),
            ]);
    
            // Retornar una consulta vacía para evitar que falle el export
            return repuesto::query()->whereRaw('1 = 0'); // Esto no devolverá ningún registro
        }
    }

    public function headings(): array
    {
        return [
            "Codigo interno",
            "Proveedor",
            "Cod Rep Prov",
            "Stock Total"
        ];
    }

    public function title(): string
    {
        return 'Repuestos_' . $this->id_proveedor;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:D1'; // Incluye la nueva columna D
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(16);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('DD4B39');
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
