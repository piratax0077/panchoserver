<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class carrito_transferido extends Model
{
    protected $table='carrito_transferido';
    protected $fillable=[
        'usuarios_id',
        'cajeros_id',
        'cliente_id',
        'item',
        'id_repuestos',
        'id_local',
        'id_unidad_venta',
        'cantidad',
        'pu',
        'subtotal_item',
        'descuento_item',
        'total_item',
        'titulo'
    ];

    public function dame_todo_carrito()
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'repuestos.oferta',
                                    'repuestos.id as idrepuesto',
                                    'users.name')
                                    ->where('carrito_transferido.cajeros_id',Auth::user()->id)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    // ->join('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->join('users','carrito_transferido.usuarios_id','users.id')
                                    
                                    ->distinct()
                                    ->groupBy('carrito_transferido.cliente_id')
                                    ->orderBy('carrito_transferido.id','asc')
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_todo_carrito_cliente($cliente_id)
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.oferta',
                                    'repuestos.descripcion',
                                    'repuestos.id_familia',
                                    'repuestos.cod_repuesto_proveedor',
                                    'ofertas_pagina_web.activo',
                                    'marcarepuestos.marcarepuesto',
                                    'locales.local_nombre')
                                    ->where('carrito_transferido.cajeros_id',Auth::user()->id)
                                    ->where('carrito_transferido.cliente_id',$cliente_id)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                    ->join('locales','carrito_transferido.id_local','locales.id')
                                    ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->orderBy('carrito_transferido.item','ASC')
                                    ->distinct()
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_total()
    {
        $total = carrito_transferido::where('cajeros_id', Auth::user()->id)->sum('total_item');
        return $total;
    }

    public function dame_total_cliente($cliente_id){
        $total = carrito_transferido::where('cajeros_id', Auth::user()->id)->where('cliente_id',$cliente_id)->sum('total_item');
        return $total;
    }
}
