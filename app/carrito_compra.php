<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;
use App\repuesto;
use Debugbar;
use Illuminate\Support\Facades\Auth;

class carrito_compra extends Model
{
    protected $table='carrito_compras';
    protected $fillable=[
        'usuarios_id',
        'item',
        'id_repuestos',
        'id_local',
        'id_unidad_venta',
        'cantidad',
        'pu_neto',
        'pu',
        'subtotal_item',
        'descuento_item',
        'total_item'
    ];

    public function dame_total()
    {
        $total = carrito_compra::where('usuarios_id', Auth::user()->id)->sum('total_item');
        return $total;
    }

    public function dame_neto()
    {
        $total = carrito_compra::where('usuarios_id', Auth::user()->id)->sum('total_item');
        $neto=$total/ (1 + Session::get('PARAM_IVA'));
        return $neto;
    }

    public function dame_iva()
    {
        $total = carrito_compra::where('usuarios_id', Auth::user()->id)->sum('total_item');
        $neto=$total/ (1 + Session::get('PARAM_IVA'));
        $iva=$total-$neto;
        //Debugbar::addMessage('carrito_compra->dame_iva','depurador');
        return $iva;
    }

    public function dame_todo_carrito()
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        try {
            $todo_el_carrito=carrito_compra::select('carrito_compras.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'repuestos.oferta',
                                    'repuestos.id as idrepuesto',
                                    'repuestos.id_familia',
                                    'repuestos.cod_repuesto_proveedor',
                                    'locales.local_nombre',
                                    'ofertas_pagina_web.activo',
                                    'marcarepuestos.marcarepuesto')
                                    ->where('carrito_compras.usuarios_id',Auth::user()->id)
                                    ->join('repuestos','carrito_compras.id_repuestos','repuestos.id')
                                    ->join('locales','carrito_compras.id_local','locales.id')
                                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                    ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->orderBy('carrito_compras.item','ASC')
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }



}
