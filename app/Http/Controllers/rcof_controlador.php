<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Debugbar;
use App\rcof_boletas;
use App\boleta;
use App\boleta_detalle;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\permissions_detail;
use Session;
use Carbon\Carbon;
use \App\servicios_sii\EnvioDte;
use \App\servicios_sii\ConsumoFolio;
use App\servicios_sii\ClsSii;
use App\servicios_sii\Auto;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Sii;
use App\servicios_sii\Dte;
use App\servicios_sii\XML;

use Illuminate\Support\Facades\Auth; 

class rcof_controlador extends Controller
{
    function dias_del_mes($month, $year)
    {
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    public static function dame_firma()
    {
        $clave1="pancho1048";
        $clave2="pancho1048";
        
        $archivo_firma=base_path().'/cert/josetroncoso.p12';
        
        if(is_readable($archivo_firma))
        {
            
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            
            $Firma=new FirmaElectronica($firma_config);
            return $Firma;
        }else{
            return false;
        }

    }

    public function rcof_boletas(){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 7 && $permiso_detalle->usuarios_id == Auth::user()->id){
                    return view('libros.rcof_boletas');
                    }
            }
        $user = Auth::user();
        if($user->rol->nombrerol === "contabilidad" || $user->rol->nombrerol === "Administrador"){
            return view('libros.rcof_boletas');
        }else{
            return redirect('home');
        }
        
    }

    public function crear_rcof($data){
        list($mes,$año)=explode("&",$data);
        $hay=rcof_boletas::where('activo',1)
                        ->whereraw('MONTH(fecha_emision)=?',[$mes])
                        ->whereraw('YEAR(fecha_emision)=?',[$año])
                        ->get();
        if($hay->count()>0){
            $rcof=rcof_boletas::where('activo',9)->get(); //solo para que dé cero
            $mensaje="RCOF para el período ".$mes."-".$año." ya fue creado.";
            return view('fragm.rcof_boletas_listado',compact('rcof','mensaje'))->render();
        }else{
            $dias=$this->dias_del_mes($mes,$año);
            for($dia=1;$dia<=$dias;$dia++){
                $rcof=new rcof_boletas;
                $rcof->fecha_emision=$año."-".$mes."-".$dia;
                $rcof->num_rcof=$año.$mes.$dia;
                $rcof->fecha_inicio=$año."-".$mes."-".$dia;
                $rcof->fecha_final=$año."-".$mes."-".$dia;
                $rcof->secuencia=1;
                $rcof->estado=0; //0 SIN PROCESAR 1 PROCESADO
                $rcof->neto_39=0;
                $rcof->iva_39=0;
                $rcof->exento_39=0;
                $rcof->total_39=0;
                $rcof->folios_emitidos_39=0;
                $rcof->folios_anulados_39=0;
                $rcof->folios_utilizados_39=0;
                $rcof->rango_inicial_39=0;
                $rcof->rango_final_39=0;
                $rcof->neto_61=0;
                $rcof->iva_61=0;
                $rcof->exento_61=0;
                $rcof->total_61=0;
                $rcof->folios_emitidos_61=0;
                $rcof->folios_anulados_61=0;
                $rcof->folios_utilizados_61=0;
                $rcof->rango_inicial_61=0;
                $rcof->rango_final_61=0;
                $rcof->trackid='---';
                $rcof->url_xml="---";
                $rcof->estado_sii="NO RECIBIDO";
                $rcof->detalle="---";
                $rcof->activo=1;
                $rcof->usuarios_id=Auth::user()->id;
                $rcof->save();
            }
            return $this->listar_rcof($data);
        }


    }

    public function listar_rcof($data){
        /*
        $hoy=Carbon::now();
        $hoy_str=Carbon::now()->toDateString();//date("Y-m-d",strtotime('yesterday'));
        $atras=$hoy->subDay(30);
        $atras_str=$atras->toDateString();
        */
        list($mes,$año)=explode("&",$data);
        $hoy=date("Y-m-d",strtotime('yesterday'));

        $hay=rcof_boletas::where('activo',1)
                        ->whereraw('MONTH(fecha_emision)=?',[$mes])
                        ->whereraw('YEAR(fecha_emision)=?',[$año])
                        ->get();

        $rcof=rcof_boletas::where('activo',1)
                        ->where('fecha_emision','<=',$hoy)
                        ->whereraw('MONTH(fecha_emision)=?',[$mes])
                        ->whereraw('YEAR(fecha_emision)=?',[$año])
                        ->orderBy('id','DESC')
                        ->get();
        $crear=false;

        if($hay->count()>0 && $rcof->count()==0){
            $mensaje="PERÍODO CREADO PERO NO DISPONIBLE AÚN... ";
        }else if($hay->count()>0 && $rcof->count()>0){
            $mensaje="Listado...";
        }else if($hay->count()==0 && $rcof->count()==0){
            $mensaje="NO EXISTEN DATOS PARA ESTE PERÍODO.";
            $crear=true;
        }else if($hay->count()==0 && $rcof->count()>0){
            $mensaje="Error General... comunicarse con el ADMIN";
        }
        return view('fragm.rcof_boletas_listado',compact('rcof','mensaje','crear'))->render();
    }


    public function procesar($fecha){

        

        //Verificar si ya fue procesado
        $estado_rcof=rcof_boletas::where('activo',1)
                    ->where('fecha_emision',$fecha)
                    ->value('estado');

        
        if($estado_rcof>0){
            $rs=['estado'=>'ATENCION','mensaje'=>'RCOF del '.$fecha.' ya fué procesado.'];
            return json_encode($rs);
        }

        //Buscamos si existe el archivo para determinar la secuencia de envío
        $secuencia=rcof_boletas::where('activo',1)
                ->where('fecha_emision',$fecha)
                ->max('secuencia');
   
                

        $boletas=boleta::select('num_boleta','neto','iva','total','trackid','url_xml','estado_sii','fecha_emision')
                ->where('fecha_emision',$fecha)
                ->where('activo',1)
                ->get();

        $hay_boletas=$boletas->count();

 

        $notas_de_credito=nota_de_credito::select('num_nota_credito','neto','iva','total','trackid','url_xml','estado_sii','fecha_emision')
                        ->whereraw('SUBSTRING(docum_referencia,1,2)=?',['bo'])
                        ->where('fecha_emision',$fecha)
                        ->where('activo',1)
                        ->get();

        $hay_nc=$notas_de_credito->count();

        $ConsumoFolio = new ConsumoFolio();
        
        $Firma=self::dame_firma();
       
        if($Firma===false){
            $rs=['estado'=>'ERROR','mensaje'=>'Error en archivo de Firma'];
            return json_encode($rs);
        }
        
        $ConsumoFolio->setDocumentos([39,61]);

        try {
            if($hay_boletas>0){
                foreach($boletas as $boleta){
                    if($boleta->estado_sii=='ACEPTADO'){
                        $url=base_path().'/xml/generados/boletas/'.$boleta->url_xml;
                        $archivo=$this->xml_to_array($url);
                        $data=$archivo['SetDTE']['DTE']['Documento'];
                        $data['Encabezado']['Totales']['MntTotal']=0;
                        $dte=new Dte($data);
                        $resumen=$dte->getResumen();
                        
                        $ConsumoFolio->agregar($resumen);
                    }

                    if($boleta->estado_sii=='ANULADO'){
                        $resumen_anulado =  [
                            'TpoDoc' => 39,
                            'NroDoc' => $boleta->num_boleta,
                            'TasaImp' => 0,
                            'FchDoc' => $boleta->fecha_emision,
                            'RUTDoc' => false,
                            'RznSoc' => false,
                            'MntExe' => false, //original false
                            'MntNeto' => false, //original false
                            'MntIVA' => false,
                            'MntTotal' => false,
                            'Anulado'=> true
                        ];
                        $ConsumoFolio->agregar($resumen_anulado);
                    }
   
                }
            }
            
            if($hay_nc>0){
                foreach($notas_de_credito as $nota_de_credito){
                    $url=base_path().'/xml/generados/notas_de_credito/'.$nota_de_credito->url_xml;
                    $archivo=$this->xml_to_array($url);
                    $data=$archivo['SetDTE']['DTE']['Documento'];
                    //Documento Encabezado Totales
                    // MntNeto MntExe TasaIVA IVA MntTotal
                    $data['Encabezado']['Totales']['MntNeto']=0; //para que en el resumen no salga doble, hacer lo mismo en boletas y quitar el codigo extra que se puso en dte
                    $dte=new Dte($data);
                    $resumen=$dte->getResumen();
                    $ConsumoFolio->agregar($resumen);
                }
            }
            
            if($hay_boletas==0 && $hay_nc==0){ // los días que no se emiten boletas ni nc
               
                $caratula = [
                    'RutEmisor' => str_replace(".","",Session::get('PARAM_RUT')),
                    'RutEnvia' =>str_replace(".","",Session::get('PARAM_RUT_ENVIA')),
                    'FchResol' => Session::get('PARAM_RESOL_FEC'),
                    'NroResol' => intval(Session::get('PARAM_RESOL_NUM')),
                    'FchInicio'=> $fecha,
                    'FchFinal'=> $fecha,
                    'SecEnvio'=>$secuencia
                ];
            }else{
                $caratula = [
                    'RutEmisor' => str_replace(".","",Session::get('PARAM_RUT')),
                    'RutEnvia' =>str_replace(".","",Session::get('PARAM_RUT_ENVIA')),
                    'FchResol' => Session::get('PARAM_RESOL_FEC'),
                    'NroResol' => intval(Session::get('PARAM_RESOL_NUM')),
                    'SecEnvio'=>$secuencia
                ];
            }

            
            $ConsumoFolio->setCaratula($caratula);
            $ConsumoFolio->setFirma($Firma);
            
            $xml=$ConsumoFolio->generar();
            $nombre='rcof_'.$fecha.'.xml';
            $file=base_path().'/xml/generados/rcof/'.$nombre;
            file_put_contents($file, $xml);

            $rcof=rcof_boletas::where('activo',1)
                ->where('fecha_emision',$fecha)
                ->where('secuencia',$secuencia)
                ->first();

            if(is_null($rcof)){
                $rs=['estado'=>'ERROR','mensaje'=>'No se encontró RCOF para la fecha '.$fecha." y secuencia ".$secuencia];
                return json_encode($rs);
            }
            $rcof->estado=1; //PROCESADO
            $xml_rcof=$this->xml_to_array($file);

            //boletas
            $rcof->neto_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntNeto'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntNeto']:0;
            $rcof->iva_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntIva'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntIva']:0;
            $rcof->total_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntTotal'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['MntTotal']:0;
            $rcof->folios_emitidos_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosEmitidos'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosEmitidos']:0;
            $rcof->folios_anulados_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosAnulados'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosAnulados']:0;
            $rcof->folios_utilizados_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosUtilizados'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['FoliosUtilizados']:0;
            $rcof->rango_inicial_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['RangoUtilizados']['Inicial'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['RangoUtilizados']['Inicial']:0;
            $rcof->rango_final_39=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['RangoUtilizados']['Final'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][0]['RangoUtilizados']['Final']:0;

            //notas de crédito
            $rcof->neto_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntNeto'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntNeto']:0;
            $rcof->iva_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntIva'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntIva']:0;
            $rcof->total_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntTotal'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['MntTotal']:0;
            $rcof->folios_emitidos_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosEmitidos'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosEmitidos']:0;
            $rcof->folios_anulados_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosAnulados'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosAnulados']:0;
            $rcof->folios_utilizados_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosUtilizados'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['FoliosUtilizados']:0;
            $rcof->rango_inicial_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['RangoUtilizados']['Inicial'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['RangoUtilizados']['Inicial']:0;
            $rcof->rango_final_61=isset($xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['RangoUtilizados']['Final'])?$xml_rcof['DocumentoConsumoFolios']['Resumen'][1]['RangoUtilizados']['Final']:0;
            
            $rcof->url_xml=$nombre;
            $rcof->usuarios_id=Auth::user()->id;
            
            $rcof->save();
            $rs=['estado'=>'OK','mensaje'=>'PROCESADO...'];
        } catch (\Exception $e) {
            $rs=['estado'=>'ERROR','mensaje'=>$e->getMessage()];

        }

        
        return json_encode($rs);


        // ******************************************************************
        $último_hoy=boleta::selectRaw('MAX(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
            ->where('fecha_emision',$fecha)
            ->where('activo',1)
            ->where('estado_sii','ACEPTADO')
            ->value('rs');




            if(is_null($último_hoy)) $último_hoy=0; //significa que hoy no hay emisión
            //PROCESANDO EL 29 DE DIC 2020... REVISAR
            //Debugbar::info('anterior: '.$último_anterior);
            //Debugbar::info('primero hoy: '.$primero_hoy);
            //Debugbar::info('ultimo hoy: '.$último_hoy);


            if($último_hoy==0){
                //sin emisión; procesar cero movimiento
                $rs=['estado'=>'xuxi','mensaje'=>'no se emitieron boletas'];
            }else if($último_anterior > 0 && $último_hoy>$último_anterior){
                //verificamos si falta alguno
                $faltantes=[];

                $rango_hoy=[];
                $rango_hoy_rs=boleta::selectRaw('num_boleta')
                                    ->whereRaw('activo=? and num_boleta>=? and num_boleta<=?',[1,$último_anterior,$último_hoy])
                                    ->get()
                                    ->toArray();

                foreach($rango_hoy_rs as $r) array_push($rango_hoy,$r['num_boleta']);
                $siguiente=$último_anterior+1;
                for($i=$siguiente;$i<$último_hoy;$i++){
                    if(!in_array($i,$rango_hoy)) array_push($faltantes,$i);
                }

                if(count($faltantes)>0){
                    //hay faltantes... registrarlo como anulado para que luego figure en el rcof y sea contabilizado
                    //avisar al usuario que hay faltantes
                    //crear otra pantalla para revisar los faltantes
                    $rs=['estado'=>'xuxi','mensaje'=>'hay faltantes'];
                    //Debugbar::info('FALTANTES');
                    //Debugbar::info($faltantes);
                }else{
                    $rs=['estado'=>'xuxi','mensaje'=>'no hay faltantes'];
                }
            }

        /********* */

        //obtener el último número anterior (de $fecha-1)
        $lafecha=new Carbon($fecha);
        $lafecha_anterior=$lafecha->subDay()->toDateString();

        $max_dias=50;
        $dia=1;
        do{

            $último_anterior=boleta::selectRaw('MAX(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                                    ->where('fecha_emision',$lafecha_anterior)
                                    ->where('activo',1)
                                    //->where('estado_sii','ACEPTADO')
                                    ->value('rs');
            if(intval($último_anterior)==0){
                $lafecha_anterior=$lafecha->subDay()->toDateString();
                $dia++;
            }
            if($dia==$max_dias) break;
        }while(intval($último_anterior)==0);

        if($dia==$max_dias){
            $rs=['estado'=>'xuxi','mensaje'=>'Más de 50 días atras sin movimiento'];
            return json_encode($rs);
        }
        /*
        $primero_hoy=boleta::selectRaw('MIN(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                                    ->where('fecha_emision',$fecha)
                                    ->where('activo',1)
                                    ->where('estado_sii','ACEPTADO')
                                    ->value('rs');

        if(is_null($primero_hoy)) $primero_hoy=0; //significa que hoy no hay emisión
        */

        $último_hoy=boleta::selectRaw('MAX(CONVERT(num_boleta,UNSIGNED INTEGER)) as rs')
                                    ->where('fecha_emision',$fecha)
                                    ->where('activo',1)
                                    ->where('estado_sii','ACEPTADO')
                                    ->value('rs');

        if(is_null($último_hoy)) $último_hoy=0; //significa que hoy no hay emisión
        //PROCESANDO EL 29 DE DIC 2020... REVISAR
        //Debugbar::info('anterior: '.$último_anterior);
        //Debugbar::info('primero hoy: '.$primero_hoy);
        //Debugbar::info('ultimo hoy: '.$último_hoy);


        if($último_hoy==0){
            //sin emisión; procesar cero movimiento
            $rs=['estado'=>'xuxi','mensaje'=>'no se emitieron boletas'];
        }else if($último_anterior > 0 && $último_hoy>$último_anterior){
            //verificamos si falta alguno
            $faltantes=[];

/*
            //revisar si el primero de hoy, continua inmediatamente del último_anterior
            if(($primero_hoy-$último_anterior)>1){
                Debugbar::info('rango intermedio');

                $rango_intermedio_rs=boleta::selectRaw('num_boleta')
                                            ->whereRaw('activo=? and num_boleta>=? and num_boleta<=?',[1,$último_anterior,$primero_hoy])
                                            ->get()
                                            ->toArray();
                $rango_intermedio=[];
                foreach($rango_intermedio_rs as $r){
                    array_push($rango_intermedio,$r['num_boleta']);
                }

                for($j=$último_anterior;$j<=$primero_hoy;$j++){
                    if(!in_array($j,$rango_intermedio)) array_push($faltantes,$j);
                }
            }
*/
            $rango_hoy=[];
            $rango_hoy_rs=boleta::selectRaw('num_boleta')
                                ->whereRaw('activo=? and num_boleta>=? and num_boleta<=?',[1,$último_anterior,$último_hoy])
                                ->get()
                                ->toArray();

            foreach($rango_hoy_rs as $r) array_push($rango_hoy,$r['num_boleta']);
            $siguiente=$último_anterior+1;
            for($i=$siguiente;$i<$último_hoy;$i++){
                if(!in_array($i,$rango_hoy)) array_push($faltantes,$i);
            }

            if(count($faltantes)>0){
                //hay faltantes... registrarlo como anulado para que luego figure en el rcof y sea contabilizado
                //avisar al usuario que hay faltantes
                //crear otra pantalla para revisar los faltantes
                $rs=['estado'=>'xuxi','mensaje'=>'hay faltantes'];
                //Debugbar::info('FALTANTES');
                //Debugbar::info($faltantes);
            }else{
                $rs=['estado'=>'xuxi','mensaje'=>'no hay faltantes'];
            }
        }

       // return json_encode($rs);






    }

    public function ver_detalle($fecha){
        $rpta="";
        $url=base_path().'/xml/generados/rcof/rcof_'.$fecha.'.xml';
        $archivo=$this->xml_to_array($url);

        for($i=0;$i<count($archivo['DocumentoConsumoFolios']['Resumen']);$i++){
            $TipoDocumento=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['TipoDocumento'];
            if($TipoDocumento=='39'){
                $rpta.="<b>BOLETAS</b><br>";
                $rpta.="Total: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['MntTotal']."<br>";
                $rpta.="Emitidos: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosEmitidos']."<br>";
                $rpta.="Anulados: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosAnulados']."<br>";
                $rpta.="Utilizados: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosUtilizados']."<br>";

                if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'])){
                    $rpta.="Rangos Utilizados:<br>";
                    //Debugbar::info($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']);
                    if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Inicial'])){ //hay un solo rango
                        $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Inicial'];
                        $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Final'];
                        $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";
                    }else{
                        for($j=0;$j<count($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']);$j++){
                            $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'][$j]['Inicial'];
                            $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'][$j]['Final'];
                            $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";

                        }
                    }
                }else{
                    $rpta.="Rangos Utilizados: 0<br>";
                }


                if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'])){
                    $rpta.="Rangos Anulados:<br>";
                    if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Inicial'])){ //hay un solo rango
                        $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Inicial'];
                        $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Final'];
                        $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";
                    }else{
                        for($j=0;$j<count($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']);$j++){
                            $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'][$j]['Inicial'];
                            $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'][$j]['Final'];
                            $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";

                        }
                    }
                }else{
                    $rpta.="Rangos Anulados: 0<br>";
                }

                $rpta.="<br>"; //para hacer espacio
            }

            if($TipoDocumento=='61'){
                $rpta.="<b>NOTAS DE CRÉDITO</b><br>";
                $rpta.="Total: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['MntTotal']."<br>";
                $rpta.="Emitidos: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosEmitidos']."<br>";
                $rpta.="Anulados: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosAnulados']."<br>";
                $rpta.="Utilizados: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['FoliosUtilizados']."<br>";

                if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'])){
                    $rpta.="Rangos Utilizados:<br>";
                    //Debugbar::info($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']);
                    if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Inicial'])){ //hay un solo rango
                        $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Inicial'];
                        $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']['Final'];
                        $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";
                    }else{
                        for($j=0;$j<count($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados']);$j++){
                            $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'][$j]['Inicial'];
                            $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoUtilizados'][$j]['Final'];
                            $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";

                        }
                    }
                }else{
                    $rpta.="Rangos Utilizados: 0<br>";
                }


                if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'])){
                    $rpta.="Rangos Anulados:<br>";
                    if(isset($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Inicial'])){ //hay un solo rango
                        $rpta.="Inicial: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Inicial'];
                        $rpta.=" Final: ".$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']['Final']."<br";
                    }else{
                        for($j=0;$j<count($archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados']);$j++){
                            $inicial=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'][$j]['Inicial'];
                            $final=$archivo['DocumentoConsumoFolios']['Resumen'][$i]['RangoAnulados'][$j]['Final'];
                            $rpta.="&nbsp;&nbsp;&nbsp;(".($final-$inicial+1).") Inicial: ".$inicial." Final: ".$final."<br>";

                        }

                    }
                }else{
                    $rpta.="Rangos Anulados: 0<br>";
                }
            }

        }

        return $rpta;
    }

    public function enviar_sii($fecha){

        $rcof=rcof_boletas::where('activo',1)
                                ->where('fecha_emision',$fecha)
                                ->first();


        if(is_null($rcof)){
            $rs=['estado'=>'ERROR','mensaje'=>'No se encontró RCOF de fecha'.$fecha];
            return json_encode($rs);
        }

        //enviar solo si fue procesado
        if($rcof->estado==0){
            $rs=['estado'=>'ERROR','mensaje'=>'Debe procesar primero'];
            return json_encode($rs);
        }

        if($rcof->estado>1){
            $rs=['estado'=>'ERROR','mensaje'=>'RCOF ya fué enviado...'];
            return json_encode($rs);
        }

        $url=base_path().'/xml/generados/rcof/'.$rcof->url_xml;
        $xml=file_get_contents($url);
        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT_ENVIA'));
        $RutEmisor= str_replace(".","",Session::get('PARAM_RUT'));
        try {
            $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$xml);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if($rs['estado']=='OK'){
            $rcof->estado_sii="RECIBIDO";
            $rcof->estado=2;
            $rcof->trackid=$rs['trackid'];
            $rcof->detalle=$rs['mensaje'];
            $rcof->usuarios_id=Auth::user()->id;
        }else{
            $rcof->estado_sii=$rs['estado'];
            $rcof->detalle=$rs['mensaje'];
            $rcof->estado=9;
        }
        $rcof->save();
        return json_encode($rs);
    }

    function actualizar_estado_BD($info){
        list($estado,$mensaje,$TrackID)=explode("-",$info);
        $rcof=rcof_boletas::where('activo',1)
                            ->where('trackid',$TrackID)
                            ->first();
        if(!is_null($rcof)){
            if($rcof->estado==2){
                if($estado=="EPR"){
                    $rcof->estado_sii="ACEPTADO";
                }else{
                    $rcof->estado_sii="RECHAZADO";
                }
                //$rcof->estado=3; NO CAMBIARLO PARA PODER VER SU ESTADO VARIAS VECES //Revisado por el SII
                $rcof->detalle=$mensaje;
                $rcof->save();
                $rs=['estado'=>'OK','mensaje'=>'Estado Actualizado.'];
            }elseif($rcof->estado==3){
                $rs=['estado'=>'ATENCION','mensaje'=>'Estado ya fue Actualizado.'];
            }elseif($rcof->estado<2){
                $rs=['estado'=>'ATENCION','mensaje'=>'Debe Procesar y Enviar el RCOF'];
            }

        }else{
            $rs=['estado'=>'ERROR','mensaje'=>'No existe TrackID '.$TrackID];
        }

        return json_encode($rs);
    }

    private function xml_to_array($url){
        $xml=simplexml_load_string(file_get_contents($url));
        $json=json_encode($xml);
        return json_decode($json,true);
    }

    public function procesar_old($fecha){
        $boletas=boleta::select('num_boleta','neto','iva','total','trackid','url_xml')
                        ->where('fecha_emision',$fecha)
                        ->where('activo',1)
                        ->where('estado_sii','ACEPTADO')
                        ->get();

        if($boletas->count()==0){
            //debería generar xml vacio
        }else{
            //generar el xml para luego enviar a SII
            $rs=$this->generar_xml($fecha);
            if($rs['estado']=='ERROR'){
                return "<br><br><center><h3 style='color:red'>".$rs['mensaje']."</h3></center>";
            }
            if($rs['estado']=='OK'){
                $mes=date('m',strtotime($fecha));
                return $this->listar_rcof($mes);
            }
        }

    }
}
