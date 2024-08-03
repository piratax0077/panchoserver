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
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\cliente_modelo;
use App\correlativo;
use App\folio;
use App\pago;
use App\permissions_detail;
use App\servicios_sii\ClsSii;
use Illuminate\Support\Facades\Auth;
use Session;

class nota_credito_controlador extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/notacredito'){
                    return view('ventas.nota_credito');
                }
            }
            if(Auth::user()->rol->nombrerol === "Administrador"){
                return view('ventas.nota_credito');
            }else{
                return redirect('home');
            }
        } catch (\Exception $e) {
           return $e->getMessage();
        }
        
        
    }

    public function cargar_documento($dat)
    {
        //FALTA: Debe verificarse también el archivo XML que los datos coincidan entre BD y XML
        list($doc,$n,$mot)=explode("-",$dat);
        $tip_doc=substr($doc,0,2); //bo
        $num_doc=$n; //el número buscado

        //REVISAR: SE PUEDE SIMPLIFICAR LA PARTE DE BOLETA Y FACTURA POR QUE ES CASI LO MISMO

        if($tip_doc=='bo')
        {
            $documento="Boleta";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            $hay=nota_de_credito::where('docum_referencia','LIKE','bo*'.$num_documento.'%')->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "rLa boleta N° ".$num_doc." ya tiene nota de crédito N° ".$h['num_nota_credito']." por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=boleta::where('num_boleta',$num_doc)
                                ->where('estado_sii','ACEPTADO')
                                ->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=$buscado_doc['fecha_emision'];//Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $idcliente=$buscado_doc['id_cliente'];

                if($idcliente==0){ //boleta sin cliente
                    $cliente=cliente_modelo::where('rut','666666666')->first()->toArray();
                }else{
                    $cliente=cliente_modelo::where('id',$idcliente)->first()->toArray();
                }

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_razon_social=$cliente['razon_social'];
                $cliente_giro=$cliente['giro'];

                $cliente_direccion=$cliente['direccion'];
                $cliente_comuna=$cliente['direccion_comuna'];
                $cliente_ciudad=$cliente['direccion_ciudad'];
                $detalle=boleta_detalle::select('boletas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                        ->where('boletas_detalle.id_boleta',$buscado_doc['id'])
                                                        ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                        ->get();



                //Revisar pago de la boleta
                $pago_buscado_doc=pago::select('pagos.*','formapago.formapago')
                                ->join('formapago','pagos.id_forma_pago','formapago.id')
                                ->where('tipo_doc','bo')
                                ->where('id_doc',$buscado_doc['id'])
                                ->get();

                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_credito_documento',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_giro',
                                    'cliente_direccion',
                                    'cliente_comuna',
                                    'cliente_ciudad',
                                    'detalle',
                                    'mot',
                                    'pago_buscado_doc'
                                    ))->render();
                return $v;

            }else{
                return "rBoleta N° ".$num_doc. " no existe o no fue aceptada por el SII.";
            }
        }

        if($tip_doc=='fa')
        {
            $documento="Factura";
            $num_documento=$num_doc;
            //Buscar si no se emitió nota de crédito para este documento
            $hay=nota_de_credito::where('docum_referencia','LIKE','fa*'.$num_documento.'%')->first();
            if(!is_null($hay))
            {
                $h=$hay->toArray();
                return "rLa factura N° ".$num_doc." ya tiene nota de crédito N° ".$h['num_nota_credito']." por un valor de ".$h['total']." de fecha ".Carbon::parse($h['fecha_emision'])->format('d-m-Y')." motivo: ".$h['motivo_correccion'];
            }

            $buscado_doc=factura::where('num_factura',$num_doc)
                                ->where('estado_sii','ACEPTADO')
                                ->first();
            if(!is_null($buscado_doc))
            {
                $buscado_doc=$buscado_doc->toArray();
                $id_documento=$buscado_doc['id'];
                $fecha_documento=$buscado_doc['fecha_emision']; //Carbon::parse($buscado_doc['fecha_emision'])->format('d-m-Y');
                $cliente=cliente_modelo::where('id',$buscado_doc['id_cliente'])->first()->toArray();

                $cliente_id=$cliente['id'];
                $cliente_rut=$cliente['rut'];
                $cliente_giro=$cliente['giro'];
                $cliente_razon_social=$cliente['razon_social'];
                if(substr($cliente_rut,0,5)=='00000')
                {
                    $cliente_id="0";
                    $cliente_rut="Sin Cliente";
                    $cliente_razon_social="";
                }

                $cliente_direccion=$cliente['direccion'];
                $cliente_comuna=$cliente['direccion_comuna'];
                $cliente_ciudad=$cliente['direccion_ciudad'];
                $detalle=factura_detalle::select('facturas_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                        ->where('facturas_detalle.id_factura',$buscado_doc['id'])
                                        ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                        ->get();


                //Revisar pago de la factura
                $pago_buscado_doc=pago::select('pagos.*','formapago.formapago')
                                ->join('formapago','pagos.id_forma_pago','formapago.id')
                                ->where('tipo_doc','fa')
                                ->where('id_doc',$buscado_doc['id'])
                                ->get();


                //dd($buscado_doc);
                //return $buscado_doc['fecha_emision'];
                $v = view('fragm.nota_credito_documento',
                    compact('documento','id_documento',
                                    'num_documento',
                                    'fecha_documento',
                                    'cliente_id',
                                    'cliente_rut',
                                    'cliente_razon_social',
                                    'cliente_giro',
                                    'cliente_direccion',
                                    'cliente_comuna',
                                    'cliente_ciudad',
                                    'detalle',
                                    'mot',
                                    'pago_buscado_doc'
                                    ))->render();
                return $v;

            }else{
                return "rFactura N° ".$num_doc. " no existe o no fue aceptada por el SII.";
            }
        }

    }

    public function generar_xml(Request $r)
    {
       
        $id_cliente=$r->id_cliente;
        
        if($id_cliente==0) //boleta sin cliente
        {
            $id_cliente=$this->dame_cliente_0();
            if($id_cliente<0)
            {
                $msje="En la tabla clientes no se ha definido el cliente 0000000000";
                return "z&".$msje;
            }
        }

        

        $nume=$this->dame_correlativo();
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $estado=['estado'=>'ERROR_CAF','mensaje'=>"Nota Crédito: No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++;
            $Datos['folio_dte']=$nume;
        }
        $Datos['tipo_dte']='61';
        $Datos['id_cliente']=$id_cliente;
        //Obtener cliente
        $cliente=cliente_modelo::find($id_cliente);
    
        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
/*
2*Factura N° 45. Corregir Texto[IMPUESTOS INTERNOS servicio*LINDOS HERMOSOS*AV. PEPE TORRES 654*Arica*Arica]

*/

        if($cliente->tipo_cliente==0){ //persona natural
            $rzsoc=$cliente->nombres." ".$cliente->apellidos;
        }
        if($cliente->tipo_cliente==1){ //empresa
            $rzsoc=$cliente->razon_social;
        }

        if($r->motivo_codigo=="2"){
            $pos=strpos($r->texto_modificado,"[");
            $t=substr($r->texto_modificado,$pos);
            $t=str_replace("[","",$t);
            $t=str_replace("]","",$t);
            $texto_modificado=str_replace("*",". ",$t).".";
            list($rz,$giro,$dir,$com,$ciu)=explode("*",$t);
            $Receptor=['RUTRecep'=>$rutCliente_con_guion,
            'RznSocRecep'=>$rzsoc,
            'GiroRecep'=>$giro,
            'DirRecep'=>$dir,
            'CmnaRecep'=>$com,
            'CiudadRecep'=>$ciu
            ];
        }else{
            $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                'RznSocRecep'=>$rzsoc,
                'GiroRecep'=>$cliente->giro,
                'DirRecep'=>$cliente->direccion,
                'CmnaRecep'=>$cliente->direccion_comuna,
                'CiudadRecep'=>$cliente->direccion_ciudad
            ];
            $texto_modificado="";
        }
        $raz=$r->motivo_correccion.": ".$texto_modificado;
        $Datos['RazonRef']=substr($raz,0,89);


        //$r->docum_referencia viene asi: bo*23*11-10-2020
        list($tdoc,$ndoc,$fdoc)=explode("*",$r->docum_referencia);
        if($tdoc=='bo') $Datos['TpoDocRef']='39';
        if($tdoc=='fa') $Datos['TpoDocRef']='33';

        $Datos['FolioRef']=$ndoc;
        $Datos['FchRef']=$fdoc;
        $Datos['CodRef']=$r->motivo_codigo; //1. anula doc 2.corrige texto 3.corrige montos


        $Detalle=[];
       
        //DESJSOINIZAR
        $item=json_decode($r->items_num);
        
        $ids=json_decode($r->items_id);
        $idreps=json_decode($r->items_idrep);
        $descripciones=json_decode($r->items_descripcion);
        
        $precios=json_decode($r->items_precio);
        $cantidades=json_decode($r->items_cantidad);
        $subtotales=json_decode($r->items_subtotal); //este no tiene sentido traerlo
    
        if($r->motivo_codigo=="2"){

            $item=array('NmbItem'=>'Corrige Textos Receptor',
            'QtyItem'=>0.1,
            'PrcItem'=>0.1);
            array_push($Detalle,$item);
        //FALTA: AGREGAR DESCUENTOS CONDICIONAL

        }else{
            for($i=0;$i<count($cantidades);$i++)
            {
                if($cantidades[$i]>0) // Sólo los items que se devuelven
                {
                    //FALTA: DISCERNIR SI ES BOLETA (valores totales) O FACTURA (valores netos)
                    if($tdoc=='fa'){
                        if($cliente->porcentaje > 0){
                            $item=array('NmbItem'=>$descripciones[$i],
                            'QtyItem'=>$cantidades[$i], //FALTA: AGREGAR DESCUENTOS CONDICIONAL
                            //'PrcItem'=>$precio_neto_item);
                            'PrcItem'=>$precios[$i],
                            'DescuentoMonto' =>$cantidades[$i] * ($precios[$i] * ($cliente->porcentaje / 100)));
                        }else{
                            $item=array('NmbItem'=>$descripciones[$i],
                            'QtyItem'=>$cantidades[$i], //FALTA: AGREGAR DESCUENTOS CONDICIONAL
                            //'PrcItem'=>$precio_neto_item);
                            'PrcItem'=>$precios[$i]);
                        }
                        
                    }
                    if($tdoc=='bo'){

                        //$precio_neto_item=round($precios[$i]/(1+Session::get('PARAM_IVA')),0);
                        $item=array('NmbItem'=>$descripciones[$i],
                                    'QtyItem'=>$cantidades[$i], //FALTA: AGREGAR DESCUENTOS CONDICIONAL
                                    'PrcItem'=>$precios[$i]);

                    }
                    array_push($Detalle,$item);
                }
            }

        }
        
        
        $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array
   
        
        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre','Nota de Crédito'); //OJO: Para que se necesita?
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente', intval($id_cliente));
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

        if(Session::get('xml')==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT'));
        $RutEmisor = $RutEnvia;
        $d=Session::get('xml');
        $tipo_dte=Session::get('tipo_dte');
        $doc=base_path().'/xml/generados/notas_de_credito/'.$d;

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

                $TrackID="---";
                $estado_sii=$rs['estado'];
            }
            //guardar nota_crédito
            $docum_referencia="bo*";
            $doku_dte=strval($xml->SetDTE->DTE->Documento->Referencia->TpoDocRef);
            $folio_dte=strval($xml->SetDTE->DTE->Documento->Referencia->FolioRef);
            if($doku_dte=='33') $docum_referencia="fa*";
            $docum_referencia.=$folio_dte."*".strval($xml->SetDTE->DTE->Documento->Referencia->FchRef);

            $ncc=new nota_de_credito;
            $ncc->num_nota_credito=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
            $ncc->fecha_emision=strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
            $ncc->id_cliente=$id_cliente;
            $ncc->estado = $estado;
            $ncc->estado_sii=$estado_sii;
            $ncc->resultado_envio=$resultado_envio;
            $ncc->trackid=$TrackID;
            $ncc->url_xml=$d;
            $ncc->docum_referencia=$docum_referencia; //fa*41*2020-07-28 ejemplo
            $ncc->motivo_correccion=strval($xml->SetDTE->DTE->Documento->Referencia->CodRef)."*".strval($xml->SetDTE->DTE->Documento->Referencia->RazonRef);
            if(strval($xml->SetDTE->DTE->Documento->Referencia->CodRef)=='2'){
                $ncc->total=0;
                $ncc->neto=0;
                $ncc->iva=0;
            }else{
                $ncc->total=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
                $ncc->neto=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
                $ncc->iva=intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
            }

            $ncc->exento=0.0;
            $ncc->activo=1;
            $ncc->usuarios_id=Auth::user()->id;
            $ncc->save();


            //detalle
            //DESJSOINIZAR desde post
            if(strval($xml->SetDTE->DTE->Documento->Referencia->CodRef)!='2'){
                $item=json_decode($r->items_num);
                $ids=json_decode($r->items_id);
                $idreps=json_decode($r->items_idrep);
                $descripciones=json_decode($r->items_descripcion);
                $precios=json_decode($r->items_precio);
                $cantidades=json_decode($r->items_cantidad);
                $subtotales=json_decode($r->items_subtotal);
                //desde el xml; NroLinDet
                foreach($xml->SetDTE->DTE->Documento->Detalle as $Det){

                    $pu=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                    $total_item=round(intval($Det->MontoItem)*(1+Session::get('PARAM_IVA')),2);
                    $num_item_xml=$Det->NroLinDet;
                    $ncd=new nota_de_credito_detalle;
                    $ncd->id_nota_de_credito=$ncc->id;
                    $ncd->id_facturas_detalle=$ids[$num_item_xml-1]; //REVISAR:
                    $ncd->id_repuestos=$idreps[$num_item_xml-1]; //REVISAR:
                    $ncd->id_unidad_venta=0;
                    $ncd->id_local=Session::get('local');
                    $ncd->precio_venta=$Det->PrcItem; //round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
                    $ncd->cantidad=intval($Det->QtyItem);
                    $ncd->subtotal=$Det->MontoItem; //$total_item;
                    $ncd->descuento=0;
                    $ncd->total=$ncd->subtotal-$ncd->descuento;
                    $ncd->activo=1;
                    $ncd->usuarios_id=Auth::user()->id;
                    $ncd->repuesto_devuelto = 0;
                    $ncd->save();
                    //actualizar saldos
                    $rc = new repuestocontrolador();
                    $rc->actualiza_saldos("I", $ncd->id_repuestos, $ncd->id_local, $ncd->cantidad);

                }
            }
            $this->actualizar_correlativo("nota de crédito", $ncc->num_nota_credito);

/* ACTUALIZACIÓN: SEGÚN LOS CONTADORES, LOS PAGOS DEBEN RESPETARSE, NO DEBEN DESACTIVARSE, QUITARSE, ELIMINARSE...
            //DESACTIVAR LOS PAGOS
            if($doku_dte=='39'){
                $id_doc_pago=boleta::where('num_boleta',$folio_dte)->value('id');
                $tipo_doc_pago="bo";
            }

            if($doku_dte=='33'){
                $id_doc_pago=factura::where('num_factura',$folio_dte)->value('id');
                $tipo_doc_pago="fa";
            }

            if(!is_null($id_doc_pago)){
                pago::where('tipo_doc',$tipo_doc_pago)
                    ->where('id_doc',$id_doc_pago)
                    ->update(['activo'=>0]);
            }
*/
            //return "nc&".$ncc->id."&".$ncc->num_nota_credito."&".$ncc->fecha_emision."&".$ncc->id_cliente."&".$r->docum_referencia;

            //FALTA: Poner Referencia en el documento fuente. en Factura campo docum_referencia


        } catch (\Exception $e) {
            $ee=substr($e->getMessage(),0,300);
            $estado=['estado'=>'ERROR','mensaje'=>$ee];
            return json_encode($estado);
        }

        return json_encode($rs);
    }

    public function revisar_mail_estado($trackid)
    {
        //trackid viene desde ventas_principal.
        // tipo dte, numdoc estan en session tipo_dte y folio_dte

        $param=['trackid'=>$trackid];
        $param['tipo_dte']=Session::get('tipo_dte');
        $param['folio_dte']=Session::get('folio_dte');

        //AKI: ACTUALIZAR ESTADO...

        //aqui tal vez poner un try catch
        $rs=ClsSii::revisar_mail_estado($param);

        switch ($param['tipo_dte']){
            case '39':
                $dte=boleta::where('num_boleta',$param['folio_dte'])
                                    ->where('trackid',$param['trackid'])
                                    ->first();
            break;
            case '33':
                $dte=factura::where('num_factura',$param['folio_dte'])
                                    ->where('trackid',$param['trackid'])
                                    ->first();
            break;
            case '61':
                $dte=nota_de_credito::where('num_nota_credito',$param['folio_dte'])
                                    ->where('trackid',$param['trackid'])
                                    ->first();
            break;
        }

        //actualizar estado
        if(!is_null($dte)){ //encontrado
            $dte->estado_sii=$rs['estado'];
            $dte->estado=$rs['estado']=='ACEPTADO'?1:0;
            $dte->resultado_envio=$rs['mensaje'];
            $dte->save();
        }



        //mover el xml a carpeta enviados EJM
        //$source_file = 'foo/image.jpg';
        //$destination_path = 'bar/';
        //rename($source_file, $destination_path . pathinfo($source_file, PATHINFO_BASENAME));

        return json_encode($rs);


    }


    private function dame_correlativo()
    {
        $tip_doc="nota de crédito";
        $num=-1;
        $id_local = Session::get("local"); // es el local donde se ejecuta el terminal
        $fila=correlativo::where('id_local', $id_local)
                                    ->where('documento', $tip_doc)
                                    ->first();
        if(!is_null($fila))
        {
            $corr=$fila->correlativo;
            $max_folio=$fila->hasta;
            if($max_folio>=($corr+1)) $num=$corr;
        }
        return $num;
    }

    private function actualizar_correlativo($docu, $num)
    {
        $co = correlativo::where('documento', trim($docu))
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $co->save();
    }

    public function guardar_nota(Request $r)
    {
        $id_cliente=$r->id_cliente;
        if($id_cliente==0) //No se ha elegido cliente para cotización o boleta
        {
            $id_cliente=$this->dame_cliente_0();
            if($id_cliente<0)
            {
                $msje="En la tabla clientes no se ha definido el cliente 0000000000";
                return "z&".$msje;
            }
        }

        $nume=$this->dame_correlativo();
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            return "y";
        }else{
            $nume++;
        }

        $ref['id_cliente']=$id_cliente;
        //$r->docum_referencia viene asi: bo*23*11-10-2020
        list($tdoc,$ndoc,$fdoc)=explode("*",$r->docum_referencia);
        if($tdoc=='bo') $ref['TpoDocRef']='39';
        if($tdoc=='fa') $ref['TpoDocRef']='33';

        $ref['FolioRef']=$ndoc;
        $ref['FchRef']=$fdoc;
        $ref['CodRef']=$r->motivo_codigo; //1. anula doc 2.corrige texto 3.corrige montos
        $ref['RazonRef']=$r->motivo_correccion;

        //aqui enviar el documento al SII

        $rpta_sii=ClsSii::enviar_documento('notacredito',$nume,$ref);
        $rs=json_decode($rpta_sii,true); //el true convierte en array asociativo... IMPORTANTE...

        if($rs['estado']!="ACEPTADO")
        {
            return "s".$rs['estado'].": ".$rs['mensaje'];
        }

        $ncc=new nota_de_credito;
        $ncc->num_nota_credito=$nume;
        $ncc->fecha_emision=Carbon::today()->toDateString(); //Solo la fecha
        $ncc->id_cliente=$id_cliente;
        $ncc->docum_referencia=$r->docum_referencia;
        $ncc->motivo_correccion=$r->motivo_codigo."*".$r->motivo_correccion.$r->texto_modificado;
        $ncc->total=$r->total;
        $ncc->neto=$r->total/(1+Session::get('PARAM_IVA'));
        $ncc->iva=$ncc->total-$ncc->neto;
        $ncc->exento=0.0;
        $ncc->activo=1;
        $ncc->usuarios_id=Auth::user()->id;
        $ncc->save();

        //detalle
        //DESJSOINIZAR jajaja
        $ids=json_decode($r->items_id);
        $idreps=json_decode($r->items_idrep);
        $precios=json_decode($r->items_precio);
        $cantidades=json_decode($r->items_cantidad);
        $subtotales=json_decode($r->items_subtotal);

        for($i=0;$i<count($cantidades);$i++)
        {
            if($cantidades[$i]>0) // Sólo los items que se devuelven
            {
                $ncd=new nota_de_credito_detalle;
                $ncd->id_nota_de_credito=$ncc->id;
                $ncd->id_facturas_detalle=$ids[$i];
                $ncd->id_repuestos=$idreps[$i];
                $ncd->id_unidad_venta=0;
                $ncd->id_local=Session::get('local');
                $ncd->precio_venta=$precios[$i];
                $ncd->cantidad=$cantidades[$i];
                $ncd->subtotal=$subtotales[$i];
                $ncd->descuento=0;
                $ncd->total=$ncd->subtotal-$ncd->descuento;
                $ncd->activo=1;
                $ncd->usuarios_id=Auth::user()->id;
                $ncd->save();

                //actualizar saldos
                $rc = new repuestocontrolador();
                $rc->actualiza_saldos("I", $ncd->id_repuestos, $ncd->id_local, $ncd->cantidad);
            }
        }

        $this->actualizar_correlativo("nota crédito", $ncc->num_nota_credito);


        return "nc&".$ncc->id."&".$ncc->num_nota_credito."&".$ncc->fecha_emision."&".$ncc->id_cliente."&".$r->docum_referencia;

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

    public function dame_nota_credito($num_nc){
        $nc=nota_de_credito::where('num_nota_credito',$num_nc)
                                            ->where('estado_sii','ACEPTADO')
                                            ->first();
        if(is_null($nc))
        {
            $estado=['estado'=>'ERROR','mensaje'=>"No existe Nota de Crédito ACEPTADA por SII"];
        }else{
            $estado=['estado'=>'OK','mensaje'=>$nc];
        }


        return json_encode($estado);
    }

    public function dame_nota_credito_detalle($id_nc){
        $ncd=nota_de_credito_detalle::select('notas_de_credito_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                ->where('id_nota_de_credito',$id_nc)
                ->join('repuestos','notas_de_credito_detalle.id_repuestos','repuestos.id')
                ->get();
        if($ncd->count()>0)
        {
            $rpta=$ncd;
        }else{
            $rpta="nohay";
        }
        return $rpta;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
