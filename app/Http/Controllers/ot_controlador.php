<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ot;
use App\ot_detalle;
use App\ot_detalle_grupo;
use App\proveedor;
use Session;
use Debugbar;
use Illuminate\Support\Facades\Auth;

class ot_controlador extends Controller
{
    public function index(){
        return view('inventario.orden_transporte');
    }

    public function verificar_ot($dato){
        list($id_transportista,$numero_ot)=explode("&",$dato);
        $hay=ot::where('id_transportista',$id_transportista)
                    ->where('numero_ot',$numero_ot)
                    ->where('activo',1)
                    ->first();

        if(is_null($hay)){
            $rpta=['estado'=>'NO EXISTE'];
        }else{
            $rpta=['estado'=>'EXISTE','cab'=>$hay->toArray()];
        }
        return $rpta;
    }

    public function verificar_grupos($idg){
        $grupos=ot_detalle::select('orden_transporte_detalle.*','proveedores.id as id_prov','proveedores.empresa_nombre_corto as prov_nombre')
                    ->join('proveedores','orden_transporte_detalle.id_proveedor','proveedores.id')
                    ->where('orden_transporte_detalle.id_ot_cab',$idg)
                    ->where('orden_transporte_detalle.tipo_documento','GRUPO')
                    ->where('orden_transporte_detalle.activo',1)
                    ->get();
        if($grupos->count()>0){
            $rpta=['estado'=>'EXISTE','grupos'=>$grupos->toArray()];
        }else{
            $rpta=['estado'=>'NO EXISTE'];
        }
        return $rpta;
    }

    public function guardar_cabecera(Request $r){
        try {
            $d=json_decode($r->datos);

            //ANTES DE GUARDAR COMPROBAR SU EXISTENCIA.
            //DE EXISTIR, DEBE AVISAR QUE YA EXISTE, Y PREGUNTAR SI CONTINUA EL INGRESO DEL DETALLE

            $hay=ot::where('id_transportista',$d->id_transportista)
                    ->where('numero_ot',$d->numero_ot)
                    ->where('activo',1)
                    ->first();

            if(is_null($hay)){
                $ot=new ot;
                $ot->id_transportista=$d->id_transportista;
                $ot->numero_ot=$d->numero_ot;
                $ot->fecha_ot=$d->fecha_ot;
                $ot->fecha_recepcion=$d->fecha_recepcion;
                $ot->receptor_ot=$d->receptor_ot;
                $ot->origen_ot=$d->origen_ot;
                $ot->observaciones_ot=$d->observaciones_ot;
                $ot->activo=1;
                $ot->usuarios_id=Auth::user()->id;
                $ot->save();
                $rpta=['estado'=>'OK','id_ot_cab'=>$ot->id];
            }else{
                $rpta=['estado'=>'EXISTE','cab'=>$hay->toArray()];
            }
        } catch (\Exception $e) {
            $rpta=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
        }

        return $rpta;
    }

    public function guardar_detalle(Request $r){
        $d=json_decode($r->datos);

        try {
            $hay=ot_detalle::where('id_ot_cab',$d->id_ot_cab)
                            ->where('tipo_medida',$d->tipo_medida)
                            ->where('valor_medida',$d->valor_medida)
                            ->where('total_neto',$d->total_neto)
                            ->where('id_proveedor',$d->id_proveedor)
                            ->where('activo',1)
                            ->first();

            $hay_grupo=ot_detalle_grupo::where('tipo_documento_grupo',$d->tipo_documento)
                                        ->where('numero_doc_detalle_grupo',$d->numero_doc_detalle)
                                        ->where('id_proveedor_grupo',$d->id_proveedor)
                                        ->where('activo',1)
                                        ->first();

            if(is_null($hay) && is_null($hay_grupo)){
                $otd=new ot_detalle;
                $otd->id_ot_cab=$d->id_ot_cab;
                $otd->tipo_documento=$d->tipo_documento;
                $otd->numero_doc_detalle=$d->numero_doc_detalle;
                $otd->num_item_documento=$d->num_item_documento;
                $otd->cant_paq_detalle=$d->cant_paq_detalle;
                $otd->tipo_paquete=$d->tipo_paquete;
                $otd->id_proveedor=$d->id_proveedor;
                $otd->tipo_medida=$d->tipo_medida;
                $otd->valor_medida=$d->valor_medida;
                $otd->total_neto=$d->total_neto;
                $otd->precio_x_medida=$d->total_neto/$d->valor_medida;
                $otd->observaciones_detalle=$d->observaciones_detalle;
                $otd->activo=1;
                $otd->usuarios_id=Auth::user()->id;
                $otd->save();

                //actualizar total neto
                $total_neto=ot_detalle::where('id_ot_cab',$d->id_ot_cab)
                                        ->where('activo',1)
                                        ->sum('total_neto');

                $ot=ot::find($d->id_ot_cab);
                $ot->total_neto=$total_neto;
                $ot->save();

                $rpta=['estado'=>'OK','id_ot_det'=>$otd->id,'total_neto'=>$total_neto];

            }else{
                if(!is_null($hay_grupo)){
                    $rpta=['estado'=>'EXISTE_GRUPO'];
                }else{
                    $rpta=['estado'=>'EXISTE','id_ot_det'=>$hay->id];
                }

            }
        } catch (\Exception $e) {
            $rpta=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
        }

        return $rpta;
    }

    public function guardar_detalle_grupo(Request $r){
        $d=json_decode($r->datos);



        try {
            $hay=ot_detalle_grupo::where('tipo_documento_grupo',$d->tipo_documento_grupo)
                                ->where('numero_doc_detalle_grupo',$d->numero_doc_detalle_grupo)
                                ->where('id_proveedor_grupo',$d->id_proveedor_grupo)
                                ->where('activo',1)
                                ->first();

            $hay_detalle=ot_detalle::where('tipo_documento',$d->tipo_documento_grupo)
                                ->where('numero_doc_detalle',$d->numero_doc_detalle_grupo)
                                ->where('activo',1)
                                ->first();

            if(is_null($hay) && is_null($hay_detalle)){
                $otdg=new ot_detalle_grupo;
                $otdg->id_ot_det=$d->id_ot_det;
                $otdg->tipo_documento_grupo=$d->tipo_documento_grupo;
                $otdg->numero_doc_detalle_grupo=$d->numero_doc_detalle_grupo;
                $otdg->num_item_documento_grupo=$d->numero_item_documento_grupo;
                $otdg->cant_paq_detalle_grupo=$d->cant_paq_detalle_grupo;
                $otdg->tipo_paquete_grupo=$d->tipo_paquete_grupo;
                $otdg->id_proveedor_grupo=$d->id_proveedor_grupo;
                $otdg->observaciones_detalle_grupo=$d->observaciones_detalle_grupo;
                $otdg->activo=1;
                $otdg->usuarios_id=Auth::user()->id;
                $otdg->save();

                $tpg=ot_detalle_grupo::where('id_ot_det',$d->id_ot_det)->sum('cant_paq_detalle_grupo');
                $tidg=ot_detalle_grupo::where('id_ot_det',$d->id_ot_det)->sum('num_item_documento_grupo');
                $otd=ot_detalle::find($d->id_ot_det);
                $otd->cant_paq_detalle=$tpg;
                $otd->num_item_documento=$tidg;
                $otd->save();

                $rpta=['estado'=>'OK','total_paquetes_grupo'=>$tpg,'total_item_documento_grupo'=>$tidg];
            }else{
                if(!is_null($hay_detalle)){
                    $prov=proveedor::where('id',$hay_detalle->id_proveedor)
                                    ->where('activo',1)
                                    ->value('empresa_nombre_corto');

                    $rpta=['estado'=>'EXISTE_DETALLE','proveedor'=>$prov];
                }else{
                    $rpta=['estado'=>'EXISTE'];
                }

            }

        } catch (\Exception $e) {
            $rpta=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
        }
        return $rpta;
    }
}
