<?php
namespace App\Http\Controllers;

use Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\repuesto;
use App\boleta;
use App\boleta_detalle;
use App\factura;
use App\factura_detalle;
use App\nota_de_debito;
use App\nota_de_debito_detalle;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\cliente_modelo;
use App\correlativo;
use App\folio;
use App\permissions_detail;
use App\servicios_sii\ClsSii;
use Illuminate\Support\Facades\Auth;

use Session;

class nota_debito_controlador extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 3 && $permiso_detalle->usuarios_id == Auth::user()->id){
                    return view('ventas.nota_debito');
                    }
            }
        if(Auth::user()->rol->nombrerol === "Administrador" || Auth::user()->rol->nombrerol === "vendedor" || Auth::user()->rol->nombrerol === "Cajer@"){
            return view('ventas.nota_debito');
        }else{
            return redirect('home');
        }
        
    }

    public function cargar_documento($doc)
    {
        $tip_doc=substr($doc,0,2); //bo
        $num_doc=trim(substr($doc,2)); //el número buscado

        if($tip_doc=='bo')
        {
            $documento="Boleta";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            $hay=nota_de_debito::where('docum_referencia','bo'.$num_documento)->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "La boleta N° ".$num_doc." ya tiene nota de débito N° <b>".$h['num_nota_debito']."</b> por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=boleta::where('num_boleta',$num_doc)->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_razon_social=$cliente['razon_social'];
                if(substr($cliente_rut,0,5)=='00000')
                {
                    $cliente_id="0";
                    $cliente_rut="Sin Cliente";
                    $cliente_razon_social="";
                }

                $cliente_direccion=$cliente['direccion']."      Comuna: ".$cliente['direccion_comuna']."     Ciudad: ".$cliente['direccion_ciudad'];
                $detalle=boleta_detalle::select('boletas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                        ->where('boletas_detalle.id_boleta',$buscado_doc['id'])
                                                        ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                        ->get();


                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_debito_boleta',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_direccion',
                                    'detalle'
                                    ))->render();
                return $v;

            }else{
                return "No Existe la Boleta N° ".$num_doc;
            }
        }

        if($tip_doc=='fa')
        {
            $documento="Factura";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            $hay=nota_de_debito::where('docum_referencia','fa'.$num_documento)->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "La factura N° ".$num_doc." ya tiene nota de débito N° <b>".$h['num_nota_debito']."</b> por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=factura::where('num_factura',$num_doc)->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_razon_social=$cliente['razon_social'];
                if(substr($cliente_rut,0,5)=='00000')
                {
                    $cliente_id="0";
                    $cliente_rut="Sin Cliente";
                    $cliente_razon_social="";
                }

                $cliente_direccion=$cliente['direccion']."      Comuna: ".$cliente['direccion_comuna']."     Ciudad: ".$cliente['direccion_ciudad'];
                $detalle=factura_detalle::select('facturas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                        ->where('facturas_detalle.id_factura',$buscado_doc['id'])
                                                        ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                        ->get();


                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_debito_factura',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_direccion',
                                    'detalle'
                                    ))->render();
                return $v;

            }else{
                return "No Existe la Factura N° ".$num_doc;
            }
        }

    }

    private function dame_correlativo()
    {
        //$tip_doc="nota de débito";
        $tipo_dte='56';
        $num=-1;
        $id_local = Session::get("local"); // es el local donde se ejecuta el terminal

        $fila=correlativo::where('id_local', $id_local)
                                    ->where('tipo_dte_sii', $tipo_dte)
                                    ->first();
        if(!is_null($fila))
        {
            $corr=$fila->correlativo;
            $max_folio=$fila->hasta;
            if($max_folio>=($corr+1)) $num=$corr;
        }
        return $num;
    }

    private function actualizar_correlativo($num)
    {
        $co = correlativo::where('tipo_dte_sii', '56')
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $co->save();
    }

    private function dame_cliente_0()
    {
        $rpta=-1; //No esta definido el cliente 0000000
        $c0=cliente_modelo::where('rut','LIKE','00000%')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    public function generar_xml(Request $r)
    {
        //Sacamos toda la info de la nota de crédito
        $num_nc=$r->num_nc;

        $nc=nota_de_credito::where('num_nota_credito',$num_nc)
                                            ->where('estado_sii','ACEPTADO')
                                            ->first();
        if(is_null($nc))
        {
            $estado=['estado'=>'ERROR','mensaje'=>"No existe Nota de Crédito aceptada por el SII"];
            return json_encode($estado);
        }
        $id_cliente=$nc->id_cliente;

        $ncd=nota_de_credito_detalle::select('notas_de_credito_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                ->where('id_nota_de_credito',$nc->id)
                ->join('repuestos','notas_de_credito_detalle.id_repuestos','repuestos.id')
                ->get();

        if($ncd->count()<=0)
        {
            $estado=['estado'=>'ERROR','mensaje'=>"Detalle de Nota de Crédito No existe"];
            return json_encode($estado);
        }

        $Detalle=[];
        foreach($ncd as $i){
            $item=array('CdgItem'=>['TpoCodigo'=>'INT1','VlrCodigo'=>$i->id_repuestos],
                                    'NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>$i->pu_neto);
            array_push($Detalle,$item);
        }


        //Obtener cliente
        $cliente=cliente_modelo::find($id_cliente);
        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);

        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                'RznSocRecep'=>$cliente->razon_social,
                'GiroRecep'=>$cliente->giro,
                'DirRecep'=>$cliente->direccion,
                'CmnaRecep'=>$cliente->direccion_comuna,
                'CiudadRecep'=>$cliente->direccion_ciudad
            ];

        $nume=$this->dame_correlativo(); //kaka
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $estado=['estado'=>'ERROR_CAF','mensaje'=>"Nota Débito: No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++;
            $Datos['folio_dte']=$nume;
        }
        $Datos['tipo_dte']='56';

        $Datos['TpoDocRef']='61';
        $Datos['FolioRef']=$nc->num_nota_credito;
        $Datos['FchRef']=$nc->fecha_emision;
        $Datos['CodRef']=1; //Anulación de Nota de Crédito
        $Datos['RazonRef']="Cliente se retractó";

        $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array
        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre','Nota de Débito'); //OJO: Para que se necesita?
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente', $id_cliente);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
        }

        return json_encode($estado);
    } // fin generar_xml

    public function enviar_sii(Request $r)
    {
        $id_cliente=$r->id_cliente;

        $d=Session::get('xml');
        if($d==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT'));
        $RutEmisor = $RutEnvia;

        $tipo_dte=Session::get('tipo_dte');
        $doc=base_path().'/xml/generados/notas_de_debito/'.$d;

        $tipo_docu="nada";
        $num_docu=0;

       //Recuperar el XML Generado para enviar
        try {
            $envio=file_get_contents($doc);
            $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
            if($rs['estado']=='OK'){
                $resultado_envio=$rs['mensaje'];
                $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                $estado=0;
                $TrackID=$rs['trackid'];
                $estado_sii='RECIBIDO';
            }else{
                return json_encode($rs);
            }
            //guardar nota_debito
            $FolioRef=$xml->SetDTE->DTE->Documento->Referencia->FolioRef;
            $docum_referencia="nc*".strval($FolioRef)."*".strval($xml->SetDTE->DTE->Documento->Referencia->FchRef);

            $ndc=new nota_de_debito;
            $ndc->num_nota_debito=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
            $ndc->fecha_emision=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
            $ndc->id_cliente=$id_cliente;
            $ndc->estado = $estado;
            $ndc->estado_sii=$estado_sii;
            $ndc->resultado_envio=$resultado_envio;
            $ndc->trackid=$TrackID;
            $ndc->url_xml=$d;
            $ndc->docum_referencia=$docum_referencia; //nc*41*2020-07-28 ejemplo
            $ndc->motivo_correccion=strval($xml->SetDTE->DTE->Documento->Referencia->CodRef)."*".strval($xml->SetDTE->DTE->Documento->Referencia->RazonRef);
            $ndc->total=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
            $ndc->neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
            $ndc->iva=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);

            $ndc->exento=0.0;
            $ndc->activo=1;
            $ndc->usuarios_id=Auth::user()->id;
            $ndc->save();

            //detalle
            $nc_ref=nota_de_credito::where('num_nota_credito',$FolioRef)
                                                        ->where('estado_sii','ACEPTADO')->first();


            foreach($xml->SetDTE->DTE->Documento->Detalle as $Det){
                $pu=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                $total_item=round(intval($Det->MontoItem)*(1+Session::get('PARAM_IVA')),2);
                $num_item_xml=$Det->NroLinDet;
                $ndd=new nota_de_debito_detalle;
                $ndd->id_nota_de_debito=$ndc->id;

                $ndd->id_repuestos=$Det->CdgItem->VlrCodigo; //REVISAR: aqui debe interactuarse con el detalle de la nota de crédito
                $x=nota_de_credito_detalle::where('id_nota_de_credito',$nc_ref->id)
                                                                ->where('id_repuestos',$Det->CdgItem->VlrCodigo)
                                                                ->first();
                if(is_null($x)){
                    $ndd->id_nc_detalle=0;
                }else{
                    $ndd->id_nc_detalle=$x->id;
                }

                $ndd->id_unidad_venta=0;
                $ndd->id_local=Session::get('local');
                $ndd->precio_venta=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                $ndd->cantidad=intval($Det->QtyItem);
                $ndd->subtotal=$total_item;
                $ndd->descuento=0;
                $ndd->total=$ndd->subtotal-$ndd->descuento;
                $ndd->activo=1;
                $ndd->usuarios_id=Auth::user()->id;
                $ndd->save();
                //actualizar saldos
                $rc = new repuestocontrolador();
                $rc->actualiza_saldos("E", $ndd->id_repuestos, $ndd->id_local, $ndd->cantidad);
            }
            $this->actualizar_correlativo($ndc->num_nota_debito);

        } catch (\Exception $e) {
            $ee=substr($e->getMessage(),0,300);
            $estado=['estado'=>'ERROR','mensaje'=>$ee];
            return json_encode($estado);
        }

        return json_encode($rs);
    } // fin enviar SII

    public function actualizar_estado(Request $r){
        //viene TrackID, estado
        $nd=nota_de_debito::where('trackid',$r->TrackID)->first();
        if(!is_null($nd)){
            $nd->estado_sii=$r->estado;
            $nd->save();
            $estado=['estado'=>'OK','mensaje'=>'Estado actualizado...'];
        }else{
            $estado=['estado'=>'ERROR','mensaje'=>'No se pudo actualizar estado'];
        }


        return json_encode($estado);
    }

    public function existe_nc($nc){
        $hay=nota_de_debito::where('docum_referencia','LIKE','nc*'.$nc.'%')->first();
        if(is_null($hay)){
            $estado=['estado'=>'NO','mensaje'=>'No existe...'];
        }else{
            $estado=['estado'=>'SI','mensaje'=>'La Nota de Crédito N° '.$nc.' YA TIENE Nota de Débito N° '.$hay->num_nota_debito.' de fecha '.$hay->fecha_emision];
        }
        return json_encode($estado);
    }

}
