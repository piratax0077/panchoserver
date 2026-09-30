<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RepuestosStockMinimo implements FromCollection, WithHeadings, WithEvents
{
    use Exportable;

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
            'Proveedor',
            'Fecha Emisión',
            'Codigo interno',
            'Codigo proveedor',
            'Descripción',
            'Stock Actual',
            'Precio Compra',
            'Cantidad Solicitada',
            'N° Factura',
            'Fecha Compra',
            'Respuesta Proveedor',
            // Agrega más columnas según tus necesidades
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $highestRow; $row++) {

                    $stockActual = $sheet->getCell('F' . $row)->getValue();

                    if ((int)$stockActual === 0) {

                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFFFC7CE', // rojo claro tipo Excel
                                ],
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
