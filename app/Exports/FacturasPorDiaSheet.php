<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use App\pago;

class FacturasPorDiaSheet implements FromQuery,WithTitle,WithHeadings,ShouldAutoSize,WithEvents,WithColumnFormatting
{
    /**
    * @return \Illuminate\Database\Query\Builder
    */

    public function __construct($fecha)
    {
        $this->fecha = $fecha;
    }

    public function query()
    {
        $dia_fac = pago::query()->selectRaw('CONVERT(pagos.created_at,TIME)  as hora,"factura" as tipo_doc, facturas.num_factura as num_doc, pagos.monto as total, pagos.referencia as referencia')
            ->join('facturas', 'pagos.id_doc', 'facturas.id')
            ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?) AND pagos.referencia_pago=?', ['fa', $this->fecha, 1, 2, 5,1])
            ->orderBy('pagos.created_at', 'ASC');
        return $dia_fac;
    }

    public function title(): string
    {
        return 'Facturas Transbank ' . $this->fecha;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:E1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(16);
                $event->sheet->getDelegate()->getStyle($cellRange)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('DD4B39');
                
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }

    public function headings(): array
    {
        return ["Hora","Tipo documento","Numero de factura","Monto", "Operación"];
    }
}
