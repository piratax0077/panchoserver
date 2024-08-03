<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ot_detalle extends Model
{
    protected $table='orden_transporte_detalle';
    protected $fillable=[
        'id_ot_cab',
        'tipo_documento',
        'numero_doc_detalle',
        'num_item_documento',
        'cant_paq_detalle',
        'tipo_paquete',
        'id_proveedor',
        'tipo_medida',
        'valor_medida',
        'total_neto',
        'precio_x_medida',
        'id_transportista_factura',
        'observaciones_detalle',
        'activo',
        'usuarios_id'
    ];
}
