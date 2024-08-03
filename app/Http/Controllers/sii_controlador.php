<?php

namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Debugbar;
use App\local;
use App\correlativo;
use App\folio;
use App\servicios_sii\ClsSii;
use App\servicios_sii\Sii_boletas;
use App\servicios_sii\Auto_boletas;
use App\servicios_sii\Imap;
use App\servicios_sii\ClsAmbiente;
use App\Mail\EnviarCorreo;
use App\carrito_compra;
use App\cliente_modelo;
use App\boleta;
use App\factura;
use App\nota_de_credito;
use App\nota_de_debito;
use App\guia_de_despacho;
use App\guia_de_despacho_detalle;
use App\permissions_detail;
use App\dte_rechazados;
use Carbon\Carbon; // para tratamiento de fechas

use Illuminate\Support\Facades\Auth;

use Session;

class ClsCar2{
    public $descripcion;
    public $cantidad;
}

class sii_controlador extends Controller
{

    private function dias_del_mes($month, $year)
    {
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    public function detalle()
    {


        /* $c=cliente_modelo::select('id','rut','nombres')->get();
         return $c; */
         Session::put('PARAM_IVA',0.19);
        $doc=base_path().'/xml/generados/notas_de_credito/61_10.xml';
        $dat=file_get_contents($doc);
        $xml=new \SimpleXMLElement($dat, LIBXML_COMPACT);
        //$det=collect();
        $det=[];
        $total=0;
        $neto=0;
        $iva=0;
        foreach($xml->SetDTE->DTE->Documento->Detalle as $Det){
            $pu=round(intval($Det->PrcItem)*(1+Session::get('PARAM_IVA')),2);
            $total_item=round(intval($Det->MontoItem)*(1+Session::get('PARAM_IVA')),2);
            $total+=$total_item;
            $item=['descripcion'=>(string)$Det->NmbItem,
                        'cantidad'=>intval($Det->QtyItem),
                        'pu'=>$pu,
                        'total_item'=>$total_item
                        ];
            array_push($det,$item);

            /* $obj=new ClsCar2();
            $obj->descripcion=(string)$Det->NmbItem;
            $obj->cantidad=(string)$Det->QtyItem;
            $det->push($obj); */


        }
        return $det;
    }

    public function enviar_correo()
    {
        $correo=new EnviarCorreo();
        $response=\Mail::send($correo);
        return "Correo enviado??";
    }

    public function ver_estado()
    {

        $clave1="juana206"; // juana206 //panchorepuestos8311048
        $clave2="panchorepuestos8311048";
        $archivo_firma=base_path().'/cert/juanita_libreDTE.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new \App\servicios_sii\FirmaElectronica($firma_config);
        }else{
            $estado=['estado'=>'ERROR','mensaje'=>'No pude leer el certificado P12'];
            return json_encode($estado);
        }

        // solicitar token
        $token = \App\servicios_sii\Auto::getToken($Firma);
        if (!$token) {
            $estado=['estado'=>'ERROR','mensaje'=>'No pude obtener Token'];
            return json_encode($estado);
        }


        $xml = \App\servicios_sii\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'  => '5483206',
            'DvConsultante'   => '0',
            'RutCompania'     => '5483206',
            'DvCompania'      => '0',
            'RutReceptor'     => '60803000',
            'DvReceptor'      => 'K',
            'TipoDte'         => '39',
            'FolioDte'        => '24',
            'FechaEmisionDte' => '28072020',
            'MontoDte'        => '68319',
            'token'           => $token,
        ]);
        // si el estado se pudo recuperar se muestra
        if ($xml===false) {
            $estado=['estado'=>'ERROR','mensaje'=>'No pude obtener xml de respuesta'];
            return json_encode($estado);
        }
        // entregar estado
        $rpta=(array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
        $estado=['estado'=>$rpta['ESTADO'],'mensaje'=>$rpta['GLOSA_ESTADO']];
        
        return json_encode($estado);
    }


    public function ver_estadoUP($info)
    {
         //0101320258
        list($tipoDTE,$TrackID)=explode("&",$info);
        list($rut,$dv)=explode("-",str_replace(".","",Session::get('PARAM_RUT')));
        $estado=['estado'=>'ERROR','mensaje'=>'No se procesó...'];

        try {
            if($tipoDTE=='39'){
                
                //$params=['rut'=>$rut,'dv'=>$dv,'trackid'=>$TrackID];
                $Firma=ClsSii::dame_firma();
                $tok=Auto_boletas::getToken($Firma);

                if($tok['estado']=='OK'){
                    $token=$tok['token'];
                }else{
                    return json_encode($tok);
                }

                $params=$rut."-".$dv."-".$TrackID;
                
                $rs=Sii_boletas::request_json('GET','/boleta.electronica.envio',$params,false,$token);
                
                $est=$rs['estado'];
                
                if($est!='EPR'){
                    if($est == 'SOK'){
                        $res = "RECIBIDO";
                    }else if($est=='REC'){
                        $res="RECIBIDO";
                    }else{
                        $res="ERROR";
                    }

                    
                        // buscamos el trackid en la boleta
                        $dte = new dte_rechazados;
                        $dte->tipo_doc = $tipoDTE;
                        $dte->fecha_emision = Carbon::today()->toDateString();
                        $dte->folio_doc = 33;
                        $dte->id_cliente = Auth::user()->id;
                        
                        $dte->track_id = intval($TrackID);
                        $dte->estado = $est;
                     
                        $dte->save();
                }else{
                    
                    $aceptado=$rs['estadistica'][0]['aceptados'];
                    $rechazado=$rs['estadistica'][0]['rechazados'];
                    $reparo=$rs['estadistica'][0]['reparos'];
                    if($aceptado==1) $res="ACEPTADO";
                    if($rechazado==1) $res="RECHAZADO";
                    if($reparo==1) $res="REPARO";
                    if($res!="ACEPTADO"){
                        if(isset($rs['detalle_rep_rech'])){
                            $est.=": ".$rs['detalle_rep_rech'][0]['descripcion'].". ";
                            $est.=$rs['detalle_rep_rech'][0]['error'][0]['descripcion'];
                        }else{
                            $est.=": Envío No Aceptado";
                        }
                    }else{
                        $est.=": Envío Procesado";
                    }
                }
                $estado=['estado'=>$res,'mensaje'=>$est];
            }else{ // fin boleta 39
                //otros documentos
                $Token=ClsSii::dame_token();
                $resultado=ClsSii::dame_estado_dteUP($TrackID,$rut,$dv,$Token);
                
                //echo $resultado->asXML(); //para ver el xml en la consola del navegador
                
                if(gettype($resultado)=="object")
                {
                    $est=strval($resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]);
                    
                    if($est!="EPR")
                    {

                        $msg="SII aún procesando...espere";
                        $res="EN REVISION";
                        if($est=='RSC'){
                            $msg='Rechazado por Error en Schema';
                            $res="RECHAZADO";
                        }
                        if($est=='RFR'){
                            $msg='Rechazado por Error en Firma';
                            $res="RECHAZADO";
                        }
                        if($est=='RCT'){
                            $msg='Rechazado por Error en Carátula';
                            $res="RECHAZADO";
                        }

                        if($est=='SOK'){
                            $msg='Esquema XML OK, espere...';
                        }

                        if($est=='CRT'){
                            $msg='Carátula OK, espere...';
                        }

                        if(is_numeric($est)){
                            $res="SII MUY OCUPADO!! espere...";
                            $msg=$est."Reintente Ver Estado.";
                        }

                        $est=$msg;

                        

                        // buscamos el trackid en la boleta
                        $dte = new dte_rechazados;
                        $dte->tipo_doc = 'fa';
                        $dte->fecha_emision = Carbon::today()->toDateString();
                        $dte->folio_doc = 39;
                        $dte->id_cliente = 4;
                        $dte->track_id = $TrackID;
                        $dte->estado = $est;
                        $dte->save();
                    }else{
                        
                        $est.=": ".$resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
                        //$est.="Informados: ".$resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/INFORMADOS')[0]."\n";
                        $aceptado=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/ACEPTADOS')[0]);
                        $rechazado=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS')[0]);
                        $reparo=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/REPAROS')[0]);
                        
                        if($aceptado==1) $res="ACEPTADO";
                        if($rechazado==1) $res="RECHAZADO";
                        if($reparo==1) $res="REPARO";
                    }
                }

                if(gettype($resultado)=="string")
                {
                    //$estado="sii_controlador: Llegó un string de ".strlen($resultado)." caracteres.\n valor:".$resultado;
                    $estado=['estado'=>'ERROR','mensaje'=>'SII no responde.'];
                    $estado=json_encode($estado);
                }
            }//fin de factura dte 33 o 61

            $estado=['estado'=>$res,'mensaje'=>$est];
            $estado=json_encode($estado);
            //actualizar estado
            switch ($tipoDTE){
                case '39':
                    $dte=boleta::where('trackid',$TrackID)->first();
                break;
                case '33':
                    $dte=factura::where('trackid',$TrackID)->first();
                break;
                case '61':
                    $dte=nota_de_credito::where('trackid',$TrackID)->first();
                break;
                case '56':
                    $dte=nota_de_debito::where('trackid',$TrackID)->first();
                break;

                case '52':
                    $dte=guia_de_despacho::where('trackid',$TrackID)->first();
                break;


            }

            //actualizar estado
            if(!is_null($dte)){ //encontrado

                    if($tipoDTE=='61'){
                        list($tipoDOC,$numDOC,$fechaDOC)=explode("*",$dte->docum_referencia);
                        if($tipoDOC=='fa'){
                            $dte_ref=factura::where('num_factura',$numDOC)->first();
                            if(!is_null($dte_ref)){
                                $dte_ref->docum_referencia="nc*".$dte->num_nota_credito."*".$dte->fecha_emision."*".$dte->motivo_correccion;
                                $dte_ref->estado="2"; //Modificado con nota de crédito
                                $dte_ref->save();
                            }
                        }
                        if($tipoDOC=='bo'){
                            $dte_ref=boleta::where('num_boleta',$numDOC)->first();
                            if(!is_null($dte_ref)){
                                $dte_ref->docum_referencia="nc*".$dte->num_nota_credito."*".$dte->fecha_emision."*".$dte->motivo_correccion;
                                $dte_ref->estado="2"; //Modificado con nota de crédito
                                $dte_ref->save();
                            }
                        }
                    }
                
                    $dte->estado_sii=$res;
                    $dte->estado=$res=='ACEPTADO'?1:0;
                    $dte->resultado_envio=$est;
                    $dte->save();


            }else{
                $estado=['estado'=>'ERROR','mensaje'=>'DTE con TrackID '.$TrackID.' NO ENCONTRADO'];
                $estado=json_encode($estado);
            }
        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
            $estado=json_encode($estado);

        }

        
        return $estado;


    }

    public function ver_estadotrack($TrackID)
    {
        
        $estado=['estado'=>'ERROR','mensaje'=>'No se procesó...'];
        $estado=json_encode($estado);
        
        try {
            $Token=ClsSii::dame_token();
            
            list($rut,$dv)=explode("-",str_replace(".","",Session::get('PARAM_RUT')));
            
            $resultado=ClsSii::dame_estado_dteUP($TrackID,$rut,$dv,$Token);
            
            //echo $resultado->asXML(); //para ver el xml en la consola del navegador pestaña network, seleccionando el archivo, ver en response
            
            if(gettype($resultado)=="object")
            {
                
                $est=strval($resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]);
                
                if($est!="EPR")
                {
                    $msg="SII aún procesando...espere";
                    $res="EN REVISION";
                    if($est=='RSC'){
                        $msg='Rechazado por Error en Schema';
                        $res="RECHAZADO";
                    }
                    if($est=='RFR'){
                        $msg='Rechazado por Error en Firma';
                        $res="RECHAZADO";
                    }
                    if($est=='RCT'){
                        $msg='Rechazado por Error en Carátula';
                        $res="RECHAZADO";
                    }

                    if($est=='SOK'){
                        $msg='Esquema XML OK, espere...';
                    }

                    if($est=='CRT'){
                        $msg='Carátula OK, espere...';
                    }

                    if($est=='RCH'){ //en caso de los RCOF
                        $res="RECHAZADO";
                        $msg="".$resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]; // le pongo "" al comienzo para "convertirlo" a string jeje
                        
                    }

                    if(is_numeric($est)){
                        $res="SII MUY OCUPADO!!";
                        $msg="Reintente Ver Estado.";
                    }

                    $est=$msg;
                    //$estado=['estado'=>$est,'mensaje'=>$msg];
                    //return json_encode($estado);
                }else{ //Respuesta es EPR
                    $est.=": ".$resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
                    
                    //ESTA PARTE ES PARA DTE
                    //$est.="Informados: ".$resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/INFORMADOS')[0]."\n";

                    if(isset($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/ACEPTADOS')[0])){
                        $aceptado=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/ACEPTADOS')[0]);
                    }else{
                        $aceptado=0;
                    }

                    if(isset($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS')[0])){
                        $rechazado=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS')[0]);
                    }else{
                        $rechazado=0;
                    }

                    if(isset($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/REPAROS')[0])){
                        $reparo=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/REPAROS')[0]);
                    }else{
                        $reparo=0;
                    }

                    //$rechazado=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS')[0]);
                    //$reparo=intval($resultado->xpath('/SII:RESPUESTA/SII:RESP_BODY/REPAROS')[0]);

                    if($aceptado==0 && $rechazado==0 && $reparo==0){ // viene otra estructura, del RCOF por ejm
                        $res="EPR";
                        $est="".$resultado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0]; // le pongo "" al comienzo para "convertirlo" a string jeje
                    }

                    if($aceptado==1) $res="ACEPTADO";
                    if($rechazado==1) $res="RECHAZADO";
                    if($reparo==1) $res="REPARO";

                    //Para RCOF responde diferente
                    //aprobado: 101318336  llega estado EPR y su GLOSA
                    //rechazado: 101318250 llega estado RCH y su GLOSA
                }

                $estado=['estado'=>$res,'mensaje'=>$est];
                $estado=json_encode($estado);

            }

            if(gettype($resultado)=="string")
            {
                //$estado="sii_controlador: Llegó un string de ".strlen($resultado)." caracteres.\n valor:".$resultado;
                $estado=['estado'=>'ERROR','mensaje'=>'SII no responde.'];
                $estado=json_encode($estado);
            }


        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
            $estado=json_encode($estado);

        }


        return $estado;


    }

    public function estadodte(){

        $user = Auth::user();
        $permisos = permissions_detail::all();
        foreach ($permisos as $p) {
            if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/sii/estadodte'){
                return view('sii.estadodte');
            }
        }
        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('sii.estadodte');
        }else return redirect('home');
    }

    public function emails($TrackID)
    {
        Imap::revisar_mail($TrackID);
        return "emails: YA TA";
    }

    public function damelocales()
    {
        $locales=local::where('activo',1)->get();
        if(count($locales)>0){
            return $locales->toJSON();
        }else{
            return "cero";
        }
    }

    public function damedocumentos($idlocal)
    {
        $documentos=correlativo::select('correlativos.id as id',
                                        'correlativos.documento as nombre',
                                        'correlativos.caf_xml as archivo',
                                        'correlativos.fecha_autorizacion as fecha',
                                        'correlativos.desde as desde',
                                        'correlativos.hasta as hasta',
                                        'correlativos.correlativo as actual'
                                        )
                                    ->where('correlativos.activo',1)
                                    ->where('correlativos.id_local',$idlocal)
                                    ->get();
        if(count($documentos)>0)
        {
            return $documentos;
        }else{
            return "cero";
        }
    }

    public function guardar_caf(Request $r)
    {
       
        $archivo=$r->file('archivo');
        $nombre=$archivo->getClientOriginalName();
        
        $donde=base_path('xml/caf');
       
        $c=correlativo::find($r->iddocu);

        if(!is_null($c))
        {

            try {
                $c->caf_xml=$archivo->move($donde,$nombre);
                $arc=utf8_encode(file_get_contents($c->caf_xml));
                $xml=simplexml_load_string($arc);
                $tipo_dte_sii=(string)$xml->xpath('/AUTORIZACION/CAF/DA/TD')[0];


                if($c->tipo_dte_sii!=$tipo_dte_sii)
                {
                    unlink($c->caf_xml); //borra el archivo subido
                    return "-1";
                }

                $c->tipo_dte_sii=$tipo_dte_sii;
                $c->desde=(string)$xml->xpath('/AUTORIZACION/CAF/DA/RNG/D')[0];
                $c->hasta=(string)$xml->xpath('/AUTORIZACION/CAF/DA/RNG/H')[0];
                $c->fecha_autorizacion=$desde=(string)$xml->xpath('/AUTORIZACION/CAF/DA/FA')[0];
                $nuevo_nombre=$c->tipo_dte_sii."_L".$c->id_local."_".$c->desde."_".$c->hasta."_".$c->fecha_autorizacion.".xml";

                //Verifica que el archivo a guardar no exista al renombrarlo
                $el_archivo_nuevo=$donde."/".$nuevo_nombre;
                $xx=is_file($el_archivo_nuevo); //file_get_contents($donde."\\".$nuevo_nombre);

                if($xx===true) //existe el archivo
                {
                    unlink($c->caf_xml); //borra el archivo subido para no renombrarlo
                    return "-2";
                }

                if(!rename($c->caf_xml,$el_archivo_nuevo)===true){
                    return "-3";
                }

                $c->caf_xml=$nuevo_nombre;
                $c->save();
                return $c->id_local;

            } catch (\Exception $e) {

                return $e->getMessage();

            }

        }else{ //no encontró... pero raro

        }

    }

    public function cambiarnumeracion($dato){
        list($iddocu,$numero)=explode("&",$dato);
        $c=correlativo::find($iddocu);
        if(!is_null($c)){
            if($numero>=$c->desde-1 && $numero<=$c->hasta){
                $c->correlativo=$numero;
                $c->save();
                $estado=['estado'=>'OK','mensaje'=>'Se cambió correctamente el valor'];
                return json_encode($estado);
            }else{
                $estado=['estado'=>'ERROR','mensaje'=>'Número a cambiar fuera del rango permitido'];
                return json_encode($estado);
            }
        }else{
            $estado=['estado'=>'ERROR','mensaje'=>'No se encontró correlativo del documento'];
            return json_encode($estado);
        }
    }

    public function cargarfolios()
    {
        $locales=local::where('activo',1)->get();
        $permisos = permissions_detail::all();
        foreach($permisos as $p){
            if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/sii/cargarfolios'){
                return view('sii.cargar_folios',compact('locales'))->render();
            }
        }

        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('sii.cargar_folios',compact('locales'))->render();
        }else return redirect('home');
        
    }

    public function anularfolios()
    {
        return view('sii.anular_folios');
    }

    public function revisar_folios($dato){
        list($mes,$año,$tipo_dte)=explode("&",$dato);
        if(($mes-1==0)){
            $mes_anterior=12;
            $año_anterior=$año-1;
        }else{
            $mes_anterior=$mes-1;
            $año_anterior=$año;
        }

        //Del periodo sacar el correlativo anterior, el menor que será el inicio y el mayor que será el máximo
        if($tipo_dte==39){ //boleta
            $anterior=boleta::selectRaw('MAX(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                                    ->whereraw('MONTH(fecha_emision)=?',[$mes_anterior])
                                    ->whereraw('YEAR(fecha_emision)=?',[$año_anterior])
                                    ->where('activo',1)
                                    ->value('rs');

            if(is_null($anterior)){
                // que hacer??
                $anterior=0;
            }

            $primero=boleta::selectRaw('MIN(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                            ->whereraw('MONTH(fecha_emision)=?',[$mes])
                            ->whereraw('YEAR(fecha_emision)=?',[$año])
                            ->where('activo',1)
                            ->value('rs');

            if(is_null($primero)){
                // que hacer??
                $primero=0;
            }


            $último=boleta::selectRaw('MAX(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                            ->whereraw('MONTH(fecha_emision)=?',[$mes])
                            ->whereraw('YEAR(fecha_emision)=?',[$año])
                            ->where('activo',1)
                            ->value('rs');

            if(is_null($último)){
                // que hacer??
                $último=0;
            }

            //revisión
            $bol=boleta::selectRaw('CONVERT(num_boleta,UNSIGNED INTEGER) as numero_doc')
                            ->whereraw('MONTH(fecha_emision)=?',[$mes])
                            ->whereraw('YEAR(fecha_emision)=?',[$año])
                            ->where('activo',1)
                            ->get();

            $boletas=[];
            foreach($bol as $b){
                array_push($boletas,$b->numero_doc);
            }

            $boleta_falta="Número de Boletas no emitidas o que faltan:<br>";
            for($i=$primero;$i<=$último;$i++){
                if(!in_array($i,$boletas)){
                    $boleta_falta.=$i."<br>";
                }
            }


        }

        return "anterior ".$anterior."<br>Revisión desde ".$primero." hasta ".$último."<br>".$boleta_falta;
    }

    public function basico()
    {
        return ClsAmbiente::basico();
    }

    public function libroventas()
    {
        return ClsAmbiente::libroventas();
    }

    public function librocompras()
    {
        return ClsAmbiente::librocompras();
    }

    public function setguias()
    {
        return ClsAmbiente::setguias();
    }

    public function libroguias()
    {
        return ClsAmbiente::libroguias();
    }

    public function simulacion()
    {
        return ClsAmbiente::simulacion();
    }

    public function intercambio()
    {
        return ClsAmbiente::intercambio();
    }

    public function basico_boletas(){
        return ClsAmbiente::basico_boletas();
    }

    public function rcof_boletas(){
        return ClsAmbiente::rcof_boletas();
    }

    public function generarPDF(){
        return ClsAmbiente::generarPDF();
    //ClsAmbiente::pdfcito();
    }
}
