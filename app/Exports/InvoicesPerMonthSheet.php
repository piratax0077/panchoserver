<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;

use App\pago;

class InvoicesPerMonthSheet implements FromQuery, WithTitle
{
    private $fecha;

    public function __construct($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return pago::query()->selectRaw('CONVERT(pagos.created_at,TIME) as hora, pagos.monto as total, pagos.referencia as referencia, "boleta" as tipo_doc, boletas.num_boleta as num_doc')
        ->join('boletas', 'pagos.id_doc', 'boletas.id')
        ->whereRaw('pagos.tipo_doc=? AND pagos.fecha_pago=? AND pagos.activo=? AND (pagos.id_forma_pago=? OR pagos.id_forma_pago=?)', ['bo', $this->fecha, 1, 2, 5])
        ->orderBy('pagos.created_at', 'ASC');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Boletas';
    }
}
