<?php

namespace App\Exports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;

class RepuestosFilteredExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    use Exportable;

    protected $id_proveedor;
    protected $id_marca;
    protected $id_marca_vehiculo;
    protected $id_familia;
    protected $texto;

    public function __construct($id_proveedor = 0, $id_marca = 0, $id_familia = 0, $id_marca_vehiculo = 0, $texto = '')
    {
        $this->id_proveedor = $id_proveedor;
        $this->id_marca = $id_marca;
        $this->id_marca_vehiculo = $id_marca_vehiculo;
        $this->id_familia = $id_familia;
        $this->texto = trim((string) $texto);
    }

    public function query()
    {
        try {
            $query = repuesto::query()
                ->select(
                    'repuestos.codigo_interno',
                    'repuestos.cod_repuesto_proveedor',
                    'proveedores.empresa_nombre',
                    'repuestos.descripcion',
                    'marcarepuestos.marcarepuesto',
                    'familias.nombrefamilia',
                    'repuestos.precio_venta',
                    \DB::raw(\App\Services\StockPorLocalService::totalSql() . ' AS stock_total')
                )
                ->where('repuestos.activo', 1)
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id');

            // Filtro por proveedor
            if ($this->id_proveedor > 0) {
                $query->where('repuestos.id_proveedor', $this->id_proveedor);
            }

            // Filtro por marca
            if ($this->id_marca > 0) {
                $query->where('repuestos.id_marca_repuesto', $this->id_marca);
            }

            // Filtro por marca de vehículo (a través de las aplicaciones/similares)
            if ($this->id_marca_vehiculo > 0) {
                $query->whereIn('repuestos.id', function ($sub) {
                    $sub->select('id_repuestos')
                        ->from('similares')
                        ->where('id_marca_vehiculo', $this->id_marca_vehiculo)
                        ->where('activo', 1);
                });
            }

            // Filtro por familia
            if ($this->id_familia > 0) {
                $query->where('repuestos.id_familia', $this->id_familia);
            }

            // Búsqueda libre por código interno, descripción o código de proveedor
            if ($this->texto !== '') {
                $texto = $this->texto;
                $query->where(function ($q) use ($texto) {
                    $q->where('repuestos.codigo_interno', 'LIKE', "%{$texto}%")
                      ->orWhere('repuestos.descripcion', 'LIKE', "%{$texto}%")
                      ->orWhere('repuestos.cod_repuesto_proveedor', 'LIKE', "%{$texto}%");
                });
            }

            return $query;
        } catch (\Exception $e) {
            // Registrar el error en los logs
            \Log::error('Error en la exportación de repuestos filtrados', [
                'error' => $e->getMessage(),
            ]);
    
            // Retornar una consulta vacía para evitar que falle el export
            return repuesto::query()->whereRaw('1 = 0');
        }
    }

    public function headings(): array
    {
        return [
            "Código Interno",
            "Código Proveedor",
            "Proveedor",
            "Descripción",
            "Marca",
            "Familia",
            "Precio Venta",
            "Stock Total"
        ];
    }

    public function title(): string
    {
        $titulo = 'Repuestos_Filtrados';
        if ($this->id_proveedor > 0) {
            $titulo .= '_Prov_' . $this->id_proveedor;
        }
        if ($this->id_marca > 0) {
            $titulo .= '_Marca_' . $this->id_marca;
        }
        if ($this->id_marca_vehiculo > 0) {
            $titulo .= '_Vehic_' . $this->id_marca_vehiculo;
        }
        return $titulo;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:H1'; // Incluye todas las columnas
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('667eea');
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getFont()
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
