<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ot_detalle_grupo extends Model
{
    protected $table='orden_transporte_detalle_grupo';
    protected $fillable=[
        'id_ot_det',
        'tipo_documento_grupo',
        'numero_doc_detalle_grupo',
        'num_item_documento_grupo',
        'cant_paq_detalle_grupo',
        'tipo_paquete_grupo',
        'id_proveedor_grupo',
        'id_transportista_factura',
        'observaciones_detalle_grupo',
        'activo',
        'usuarios_id'
    ];
}
