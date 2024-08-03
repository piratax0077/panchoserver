<?php
namespace App\Http\Controllers;
//ini_set('max_execution_time', 40); //en segundos. borrar después

use Debugbar;
use Carbon\Carbon; // para tratamiento de fechas
use Illuminate\Http\Request;
use App\boleta;
use App\boleta_detalle;
use App\carrito_compra;
use App\carrito_guardado;
use App\cliente_modelo;
use App\cliente_cuenta;
use App\correlativo;
use App\folio;
use App\cotizacion;
use App\cotizacion_detalle;
use App\descuento;
use App\fabricante;
use App\factura;
use App\factura_detalle;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\familia;
use App\formapago;
use App\marcarepuesto;
use App\marcavehiculo;
use App\modelovehiculo;
use App\oem;
use App\pago;
use App\repuesto;
use App\repuestofoto;
use App\saldo;
use App\similar;
use App\User;
use App\guia_de_despacho;
use App\guia_de_despacho_detalle;
use Session;
use App\servicios_sii\ClsSii;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Auto;
use App\servicios_sii\Sii;

class ventas_controlador extends Controller
{

    /*
    private function dame_un_repuesto($id)
    {
    $repuesto=repuesto::where('repuestos.id',$id)
    ->where('repuestos.activo',1)
    ->join('familias','repuestos.id_familia','familias.id')
    ->join('marcavehiculos','repuestos.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
    ->join('modelovehiculos','repuestos.id_modelo_vehiculo','modelovehiculos.id')
    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
    ->join('paises','repuestos.id_pais','paises.id')
    ->get();

    return $repuesto;
    }

    private function damesimilares($id_repuesto)
    {
    $s=similar::select('marcavehiculos.marcanombre','modelovehiculos.modelonombre','similares.anios_vehiculo')
    ->where('similares.id_repuestos',$id_repuesto)
    ->where('similares.activo',1)
    ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
    ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
    ->get();
    return $s;
    }

    private function damefotosrepuesto($id_repuesto)
    {
    $rf=repuestofoto::select('urlfoto')
    ->where('id_repuestos',$id_repuesto)
    ->where('activo',1)
    ->get();
    return $rf;
    }

     */

    public function dame_relacionados($id_repuesto)
    {

        $repuestos = repuesto::where('repuestos_relacionados.id_repuesto_principal', $id_repuesto)
        ->where('repuestos.activo',1)
            ->join('repuestos_relacionados', 'repuestos.id', 'repuestos_relacionados.id_repuesto_relacionado')
            ->select('repuestos_relacionados.id AS id_relacionado', 'repuestos.*')
            ->get();
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();
        $criterio="relacionados";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos','criterio','tienen_foto'))->render();
        return $v;
    }

    public function damemarcas()
    {
        //usando auth en el grupo de rutas. ver web.php
        $marcas = marcavehiculo::where('activo', '=', 1)
            ->select('idmarcavehiculo', 'marcanombre', 'urlfoto')
            ->orderBy('marcanombre')
            ->get();
        $v = view('fragm.marca_vehiculos', compact('marcas'))->render();
        return $v;
    }

    public function damemodelos($idmarca)
    {
        //usando auth en el grupo de rutas. ver web.php
        $modelos = modelovehiculo::where('activo', '=', 1)
            ->where('marcavehiculos_idmarcavehiculo', $idmarca)
            ->orderBy('modelonombre')
            ->get();
        $v = view('fragm.modelo_vehiculos', compact('modelos'))->render();
        return $v;
    }

    private function revisar($repuestos,$encontrados){
        $huy=$encontrados->where('codigo_interno','CZA50')->count();
        if($repuestos->count()==0){
            $repuestos=$encontrados;
        }else{
            $repuestos=$repuestos->merge($encontrados);
        }
        //return $repuestos->unique();
        return $repuestos;
    }

    public function buscar_por_descripcion($dato){ //busqueda principal
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De ");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("  "," ",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=$descripcion;
        $descripcion_sin_guiones= str_replace("-","",$descripcion);
        $buscado_original=trim($descripcion_original);
        $buscado_sin_guiones=$descripcion_sin_guiones;
        $terminos=explode(" ",$descripcion);

        $quien_busca=User::find(Session::get('usuario_id'))->name;
        $id_buscados=\DB::table('busquedas')->insertGetId(['buscado'=>$desc,'encontrados'=>"INICIO",'fecha_hora_buscado'=>date("Y-m-d H:i:s"),'quien_busca'=>$quien_busca]);
        $conteo=[];
        $conteo["codint"]=$this->buscar_en_codint($buscado_original);
        $conteo["codprov"]=$this->buscar_en_codprov($buscado_original);
        $conteo["codoem"]=$this->buscar_en_codoem($buscado_sin_guiones);
        //$conteo["codfam"]=$this->buscar_en_codfam($buscado_original);
        $conteo["codfab"]=$this->buscar_en_codfab($buscado_original); // ERROR Malformed UTF-8 caracters, possibly incorrectly encoded exception. es por la función substr_replace que no admite caracteres UTF-8
        //$conteo["nomfab"]=$this->buscar_en_nomfab($buscado_original);
        //$conteo["marveh"]=$this->buscar_en_marveh($buscado_original);
        //$conteo["modveh"]=$this->buscar_en_modveh($buscado_original);

        $conteo["descrip"]=$this->buscar_en_descrip($buscado_original);
        $conteo["solo_descrip"]=$this->buscar_solo_en_descrip($buscado_original);

        $repuestos=Collect(); //Colección que juntará todos los resultados a entregar
        $criterio="( ";

        if($conteo['codint']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['codint']['repuestos']);
            $criterio.="codint:".$conteo["codint"]["repuestos"]->count()." ";
        }



        if($conteo['codprov']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['codprov']['repuestos']);
            $criterio.="codprov:".$conteo["codprov"]["repuestos"]->count()." ";
        }

        if($conteo['codoem']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['codoem']['repuestos']);
            $criterio.="codoem:".$conteo["codoem"]["repuestos"]->count()." ";
        }


        /*
        if($conteo['codfam']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['codfam']['repuestos']);
            $criterio.="codfam:".$repuestos->count()." ";
        }
        */



        if($conteo['codfab']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['codfab']['repuestos']);
            $criterio.="codfab:".$conteo["codfab"]["repuestos"]->count()." ";
        }



        /*
        if($conteo['nomfab']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['nomfab']['repuestos']);
            $criterio.="nomfab:".$repuestos->count()." ";
        }
        */

        if($conteo['descrip']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['descrip']['repuestos']);
            $criterio.="algor nuevo:".$conteo["descrip"]["repuestos"]->count()." ";
        }



        if($conteo['solo_descrip']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['solo_descrip']['repuestos']);
            $criterio.="en_descrip:".$conteo["solo_descrip"]["repuestos"]->count()." ";

        }



  /*
        if($conteo['marveh']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['marveh']['repuestos']);
            $criterio.="marveh:".$repuestos->count()." ";
        }
        if($conteo['modveh']['resultado']){
            $repuestos=$this->revisar($repuestos,$conteo['modveh']['repuestos']);
            $criterio.="modveh:".$repuestos->count()." ";
        }
*/
        $criterio.=" )";
        if($op==7){
            return $repuestos->count();
        }

        //$repuestos=$repuestos->sortByDesc("id_familia");
        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();



        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                    ->distinct()
                                    ->get()
                                    ->toArray();




        $desde = 'd';
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();

        //guardar en busquedas usando Query Builder
        //https://laravel.com/docs/7.x/queries#inserts
        // sin usar modelos



        if($criterio=="(  )"){
            $criterio="0";
        }
        \DB::table('busquedas')->where('id',$id_buscados)->update(['encontrados'=>$criterio]);

        return $v;
    }

    public function buscar_en_codint($buscado){
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        /*

        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=repuesto::where('repuestos.codigo_interno',$terminos[$i])
            ->where('repuestos.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="repuestos.codigo_interno=? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $encontrados=repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();
            */
            $encontrados=Collect();
            if(count($terminos)==1){
                $encontrados=repuesto::where('repuestos.codigo_interno',$buscado)
                            ->where('repuestos.activo',1)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();
            }
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }

        return $resp;
    }

    public function buscar_en_codprov($buscado){
        $buscado=trim($buscado);
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);


        $encontrados=Collect();
        if(count($terminos)==1){
            $encontrados=repuesto::where('repuestos.cod_repuesto_proveedor',$buscado)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;
        /*
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=repuesto::where('cod_repuesto_proveedor',$terminos[$i])
            ->where('repuestos.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="repuestos.cod_repuesto_proveedor LIKE ? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $encontrados=repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }
        }
        */

    }

    public function buscar_en_codfam($buscado){
        return false; // x q trae resultados generales de la familia y no lo que se especifica
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos);$i++){
            $hay=familia::where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
            ->where('familias.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
            if(count($fam)>0){
                $encontrados=repuesto::wherein('repuestos.id_familia',$fam)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_codoem($buscado){
        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);
        /*

        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=oem::where('codigo_oem',$terminos[$i])->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){

            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="oems.codigo_oem LIKE ? AND ";
            }
            $sql=$sql." oems.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $oem = oem::select('id_repuestos')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        */
        $encontrados=Collect();
        if(count($terminos)==1){
            $oem = oem::select('id_repuestos')
                        ->where('oems.codigo_oem','LIKE','%'.$buscado.'%')
                        ->where('oems.activo',1)
                        ->get()
                        ->toArray();
            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();

            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    }

    public function buscar_en_codfab($buscado){
        //repuestos_fabricantes.codigo_fab

        $encontró=false;
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $encontrados=Collect();
        //Asumiendo que ingresó solo un término, lo buscamos con el algoritmo del guion
        if(count($terminos)==1){
            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
                $encontró=true;
            }else{
                $guion="-";
                $buscado_original=$buscado;
                $buscado_sin_guiones= str_replace("-","",$buscado);
                $nf=strpos($buscado_original,$guion);
                if($nf===false){ //no hay guión, le va poniendo el guión en diferentes posiciones para buscarlo
                    for($i=1;$i<strlen($buscado_original);$i++){
                        $buskado = substr_replace($buscado_original, $guion, $i, 0); //ERROR al usar caracteres UTF-8 en las búsquedas, ejm PIÑ10
                        $numfil=fabricante::where('repuestos_fabricantes.codigo_fab','LIKE','%'.$buskado.'%')
                                            ->where('repuestos_fabricantes.activo',1)
                                            ->count();
                        if($numfil>0){
                            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buskado. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                            if($encontrados->count()>0){
                                $resp['resultado']=true;
                                $resp['repuestos']=$encontrados;
                                $encontró=true;
                            }
                            break;
                        }
                    }

                }else{ //hay guión o guiones, lo busca tal cual
                    $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                    if($encontrados->count()>0){
                        $resp['resultado']=true;
                        $resp['repuestos']=$encontrados;
                        $encontró=true;
                    }else{ //no hay tal cual, lo busca sin guiones
                        $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                        if($encontrados->count()>0){
                            $resp['resultado']=true;
                            $resp['repuestos']=$encontrados;
                            $encontró=true;
                        }
                    }
                }

            }



        } // fin 1 término

        return $resp;

        /*
        if($encontró) return $resp;

        //segunda parte: la búsqueda tiene más de 1 término
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=fabricante::where('repuestos_fabricantes.codigo_fab','%'.$terminos[$i].'%')
                            ->where('repuestos_fabricantes.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="repuestos_fabricantes.codigo_fab LIKE ? AND ";
            }
            $sql=$sql." repuestos_fabricantes.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $fab = fabricante::select('id_repuestos')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
            if(count($fab)>0){
                $encontrados=repuesto::wherein('repuestos.id',$fab)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }

        return $resp;
        */
    }

    public function buscar_en_nomfab($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }

        if(count($terminos_encontrados_fam)>0){
            $sql="";

            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }

        //marcarepuestos.marcarepuesto
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        $mr=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=marcarepuesto::where('marcarepuestos.marcarepuesto','LIKE','%'.$terminos[$i].'%')
                                ->where('marcarepuestos.activo',1)
                                ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="marcarepuestos.marcarepuesto LIKE ? AND ";
            }
            $sql=$sql." marcarepuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $mr = marcarepuesto::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            if(count($mr)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id_marca_repuesto',$mr)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $terminos_encontrados_fab=[];
                    for($i=0;$i<count($terminos);$i++){
                        $hay=fabricante::where('repuestos_fabricantes.codigo_fab','LIKE'.'%'.$terminos[$i].'%')
                                        ->where('repuestos_fabricantes.activo',1)
                                        ->count();
                        if($hay>0){
                            array_push($terminos_encontrados_fab,$terminos[$i]);
                        }
                    }
                    if(count($terminos_encontrados_fab)>0){

                        $sql="";
                        for($i=0;$i<count($terminos_encontrados_fab);$i++){
                            $sql.="repuestos_fabricantes.codigo_fab LIKE ? AND ";
                        }
                        $sql=$sql." repuestos_fabricantes.activo=?";
                        $param=[];
                        for($i=0;$i<count($terminos_encontrados);$i++){
                            $buscado_fab="%".$terminos_encontrados[$i]."%";
                            array_push($param,$buscado_fab);
                        }
                        array_push($param,1);

                        $fab = fabricante::select('id_repuestos')
                                ->whereRaw($sql,$param) //->toSql();
                                ->get()
                                ->toArray();
                        if(count($fab)>0){
                            $encontrados=repuesto::wherein('repuestos.id_marca_repuesto',$mr)
                                ->wherein('repuestos.id_marca_repuesto',$fab)
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                ->get();
                            $encontrados=$encontrados->wherein('repuestos.id_marca_repuesto',$fab)->get();
                        }
                    }


                }
            }
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }


        }
        return $resp;
    }



    public function buscar_en_marveh($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }

        if(count($terminos_encontrados_fam)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }

        //marcavehiculos.marcanombre y marcavehiculo->idmarcavehiculo
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=marcavehiculo::where('marcavehiculos.marcanombre',$terminos[$i])
                                ->where('marcavehiculos.activo',1)
                                ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="marcavehiculos.marcanombre LIKE ? AND ";
            }

            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $mv = marcavehiculo::select('idmarcavehiculo')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$mv)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();

            if(count($apli)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_modveh($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }
        if(count($terminos_encontrados_fam)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id','nombrefamilia')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }


        //modelovehiculos.modelonombre, modelovehiculo->id
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=modelovehiculo::where('modelovehiculos.modelonombre','LIKE','%'.$terminos[$i].'%')
                                ->where('modelovehiculos.activo',1)
                                ->count();

            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);
            $mv = modelovehiculo::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $apli=similar::select('id_repuestos')
                            ->wherein('id_modelo_vehiculo',$mv)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();

            if(count($apli)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_descrip($buscado){
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $buscado=trim($buscado);
        $terminos=explode(" ",trim($buscado));
        $encontrados=Collect();

        //NUEVO DESDE AQUI
        //TERMINOS EVALUADOS:
        //1. pastilla freno delantera outlander (mitsubishi outlander)
        //2. amortiguador delantero presag  (nissan presage)
        //3. correa distribucion porter (muchas familias)
        //4. FILTRO AIRE NISSAN march (trae muchas familias con 2 cantidades e incluso filtro aceite)
        //5. amortiguador march

        //PRIMERO Determinar que familia de repuestos esta buscando...
        $n_familia="";
        $n_terminos_encontrados=[];
        $decide_fam=[];
        $id_fam=0;
        for($i=0;$i<count($terminos);$i++){

            /*
            $n_hay=familia::where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            */


            $familys=familia::select('id','nombrefamilia')
                            ->where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->get();

            $n_hay=$familys->count();
            if($n_hay>0){
                array_push($n_terminos_encontrados,$terminos[$i]);
            }

            //qué id familia tiene más frecuencia
            foreach($familys as $famy)
            {
                if(!isset($decide_fam[$famy->id])) $decide_fam[$famy->id]=0;
                $decide_fam[$famy->id]++;
            }
        }

        $terer="";
        for($j=0;$j<count($n_terminos_encontrados);$j++) $terer.=$n_terminos_encontrados[$j]." ";
        $max_cant=0;
        $max_id_fam=0;
        foreach($decide_fam as $id_fam=>$cant){
            if($cant>$max_cant){
                $max_cant=$cant;
                $max_id_fam=$id_fam;
            }
        }
        $ids_max_fam=[];
        foreach($decide_fam as $id_fam=>$cant){
            if($cant==$max_cant) array_push($ids_max_fam,$id_fam);
        }

        $terminos2=[];

/*

        $terminos2=[];
        if(count($n_terminos_encontrados)>0){
            //CON LOS TÉRMINOS ENCONTRADOS DETERMINAR QUE FAMILIA
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado='%'.$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $familias_encontradas=familia::select('familias.id')->whereRaw($sql,$param)->get()->toArray();
            //hasta aqui se obtienen los ID de las familias para ponerlas en la búsqueda de repuestos del campo id_familia

            if(count($familias_encontradas)>0){
                $que_familia=familia::find($familias_encontradas[0]['id'])->value("nombrefamilia");
            }else{
                $que_familia="Ninguna";
            }

            //quitar los términos anteriores encontrados para familias
            $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos2,$j);

        }

        */

        //quitar los términos anteriores encontrados para familias
        $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
        foreach($terminos_temp as $j) array_push($terminos2,$j);


        if(count($terminos2)==0){ //no encontró repuesto para familias, y copia los terminos
            $terminos2=$terminos;
        }

        if(!isset($familias_encontradas)) $familias_encontradas=[];

        //buscar marca de vehículo
        $n_marca_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos2);$i++){
            if(strlen($terminos2[$i])>0){
                $n_hay=marcavehiculo::where('marcanombre','LIKE','%'.$terminos2[$i].'%')
                                    ->where('marcavehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos2[$i]);
                    $n_marca_vehiculo.=$terminos2[$i]." ";
                }
            }
        }

        $n_marca_vehiculo_buscado=trim($n_marca_vehiculo);
        $terminos3=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE MARCA VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="(marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ?) AND ";
            }
            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $al_inicio=$n_terminos_encontrados[$i]." %";
                $en_medio="% ".$n_terminos_encontrados[$i]." %";
                $al_final="% ".$n_terminos_encontrados[$i];
                $unico=$n_terminos_encontrados[$i];
                array_push($param,$al_inicio);
                array_push($param,$en_medio);
                array_push($param,$al_final);
                array_push($param,$unico);
            }
            array_push($param,1);
            $marca_vehiculo_encontrados=marcavehiculo::select('marcavehiculos.idmarcavehiculo')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos2,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos3,$j);
        }
        if(count($terminos3)==0){
            $terminos3=$terminos2;
        }

        if(!isset($marca_vehiculo_encontrados)) $marca_vehiculo_encontrados=[];

        //buscar modelo de vehículo
        //En terminos3 puede llegar el cilindraje del motor en formato 2.5 por ejemplo
        //y debe convertirse en 2500
        $cil_punto=[0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5,2.6,2.7,2.8,2.9,3.0,3.1,3.2,3.3,3.4,3.5,3.6,3.7,3.8,3.9,4.0,4.1,4.2,4.3,4.4,4.5,4.6,4.7,4.8,4.9,5.0,5.1,5.2,5.3,5.4,5.5,5.6,5.7,5.8,5.9,6.0,6.1,6.2,6.3,6.4,6.5,6.6,6.7,6.8,6.9,7.0];
        //$cil_coma=['0,6','0,7','0,8','0,9','1,1','1,1','1,2','1,3','1,4','1,5','1,6','1,7','1,8','1,9','2,0','2,1','2,2','2,3','2,4','2,5','2,6','2,7','2,8','2,9','3,0','3,1','3,2','3,3','3,4','3,5','3,6','3,7','3,8','3,9','4,0','4,1','4,2','4,3','4,4','4,5','4,6','4,7','4,8','4,9','5,0','5,1','5,2','5,3','5,4','5,5','5,6','5,7','5,8','5,9','6,0','6,1','6,2','6,3','6,4','6,5','6,6','6,7','6,8','6,9','7,0'];
        $cil_normal=[600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000,4100,4200,4300,4400,4500,4600,4700,4800,4900,5000,5100,5200,5300,5400,5500,5600,5700,5800,5900,6000,6100,6200,6300,6400,6500,6600,6700,6800,6900,7000];

        $n_modelo_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos3);$i++){
            if(strlen($terminos3[$i])>0){
                if(strpos($terminos3[$i],",")>0){
                    $terminos3[$i]=str_replace(',','.',$terminos3[$i]);
                }
                if(is_numeric($terminos3[$i])){
                    $valnum=$terminos3[$i];
                    settype($valnum,"float");
                    $i_punto=array_search($valnum,$cil_punto,true); //si no encuentra devuelve false, caso contrario el indice
                    if($i_punto===false){

                    }else{
                        $terminos3[$i]=$cil_normal[$i_punto];
                    }
                }


                $n_hay=modelovehiculo::where('modelonombre','LIKE','%'.$terminos3[$i].'%')
                                    ->where('modelovehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos3[$i]);
                    $n_modelo_vehiculo.=$terminos3[$i]." ";
                }
            }
        }

        $n_modelo_vehiculo_buscado=trim($n_modelo_vehiculo);
        $terminos4=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE modelo VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado="%".$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $modelo_vehiculo_encontrados=modelovehiculo::select('modelovehiculos.id')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos3,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos4,$j);

        }
        if(count($terminos4)==0){
            $terminos3=$terminos3;
        }

        if(!isset($modelo_vehiculo_encontrados)) $modelo_vehiculo_encontrados=[];


        //$resultado="familia: ".$que_familia.". MarcaVeh: ".$n_marca_vehiculo_buscado.". ModeloVeh: ".$n_modelo_vehiculo_buscado."(".count($modelo_vehiculo_encontrados).")";

//NUEVO HASTA AQUI

//familias_encontradas, marca_vehiculo_encontrados, modelo_vehiculo_encontrados
        if($id_fam>0){
            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)==0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)==0){

                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();

            }else{

                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                                    ->wherein('repuestos.id',$apli)
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();

            }

        }

        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;



    }

    public function buscar_solo_en_descrip($buscado){
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $encontrados=Collect();

        $sql="";
        for($i=0;$i<count($terminos);$i++){
            $sql.="(repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ?) AND ";
        }
        $sql=$sql." repuestos.activo=?";

        $param=[];
        for($i=0;$i<count($terminos);$i++){
            $al_inicio=$terminos[$i]." %";
            $en_medio="% ".$terminos[$i]." %";
            $al_final="% ".$terminos[$i];
            array_push($param,$al_inicio);
            array_push($param,$en_medio);
            array_push($param,$al_final);
        }
        array_push($param,1);
        $encontrados=repuesto::whereRaw($sql,$param)
                        ->where('repuestos.codigo_OEM_repuesto','<>','XPRESS')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;

    }


    public function xbuscar_por_descripcion($dato){
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("  "," ",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=$descripcion;
        $descripcion_sin_guiones= str_replace("-","",$descripcion);
        $buscado_original=$descripcion_original;
        $buscado_sin_guiones=$descripcion_sin_guiones;
        $terminos=explode(" ",$descripcion);

        $repuestos=Collect(); //Colección que juntará todos los resultados a entregar

        $nt=count($terminos); // número de términos

        if($nt==1){
            //repuestos.codigo_interno

            $encontrados = repuesto::where('repuestos.codigo_interno', 'LIKE','%'.$buscado_original.'%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
            if($encontrados->count()>0){

                //$repuestos=$repuestos->merge($encontrados);
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //repuestos.cod_repuesto_proveedor

            $encontrados = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
            if($encontrados->count()>0){

                $repuestos=$this->revisar($repuestos,$encontrados);
            }


            //repuestos_fabricantes.codigo_fab

            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }else{
                $guion="-";
                $nf=strpos($buscado_original,$guion);
                if($nf===false){ //no hay guión
                    for($i=1;$i<strlen($buscado_original);$i++){
                        $buskado = substr_replace($buscado_original, $guion, $i, 0);
                        $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                            ->count();
                        if($numfil>0){
                            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buskado. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                            if($encontrados->count()>0){

                                $repuestos=$this->revisar($repuestos,$encontrados);
                            }
                            break;
                        }
                    }

                }else{ //hay guión
                    $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                    if($encontrados->count()>0){
                        $repuestos=$this->revisar($repuestos,$encontrados);
                    }
                }

            }

            //FALTA: nombre de fabricante



            //oems.codigo_oem

            $encontrados = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_original . '%')
                                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //familias.nombrefamilia

            $fam = familia::select('id')
                ->where("familias.nombrefamilia","LIKE","%".$buscado_original."%")
                ->get()
                ->toArray();
            $encontrados = repuesto::where('repuestos.activo',1)
                                    ->wherein('repuestos.id_familia', $fam)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //modelovehiculos.modelonombre
            $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$buscado_original. '%')
                        ->get()
                        ->toArray();
            $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();
            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->where('repuestos.activo',1)
                ->wherein('repuestos.id', $aplicaciones)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->orderBy('repuestos.descripcion')
                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //repuestos.descripcion
            $encontrados = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //FALTA: marca de vehículo



        } // fin 1 término

        if($nt==2){
            //familias.nombrefamilia (hay hasta 4 términos)
            $sql="";
            for($i=0;$i<count($terminos);$i++){
                if($i==count($terminos)-1){
                    $sql.="familias.nombrefamilia LIKE ?";
                }else{
                    $sql.="familias.nombrefamilia LIKE ? AND ";
                }
            }

            $param=[];
            for($i=0;$i<count($terminos);$i++){
                $buscado="%".$terminos[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);
            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->wherein('repuestos.id_familia', $fam)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->orderBy('repuestos.descripcion')
                    ->get();


            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }



            //modelovehiculos.modelonombre
            $sql="";
            for($i=0;$i<count($terminos);$i++){
                if($i==count($terminos)-1){
                    $sql.="modelovehiculos.modelonombre LIKE ?";
                }else{
                    $sql.="modelovehiculos.modelonombre LIKE ? AND ";
                }
            }
            $modelos = modelovehiculo::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_modelo_vehiculo', $modelos)
                    ->get()
                    ->toArray();

            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->orderBy('repuestos.descripcion')
                    ->get();


            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }


            //repuestos.descripcion
            $sql="";
            //Buscar cada término en descripcion
            for($i=0;$i<count($terminos);$i++){
                $sql.="repuestos.descripcion LIKE ? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            array_push($param,1);

            $encontrados = repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();

            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //Combinaciones de 2 términos??
            //por ejm nombre fabricante y código del fabricante "mando mph45" -> PAF59

        } // fin 2 términos

        if($nt>2){
            //familias.nombrefamilia (hay hasta 4 términos)


            //modelovehiculos.modelonombre

            //repuestos.descripcion
        }

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                    ->distinct()
                                    ->get()
                                    ->toArray();
        $desde = 'd';
        $criterio="nuevo algoritmo"; //$q;
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;

/* ******************************************** */

        $sql1="";
        //Buscar cada término en descripcion
        for($i=0;$i<count($terminos);$i++){
            $sql1.="repuestos.descripcion LIKE ? AND ";
        }
        $sql1=$sql1." repuestos.activo=?";
        $param=[];
        for($i=0;$i<count($terminos);$i++){
            $buscado="%".$terminos[$i]."%";
            array_push($param,$buscado);
        }
        array_push($param,1);

        $encontrados = repuesto::whereRaw($sql1,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);//$this->revisar($repuestos,$encontrados);
        }

        //Buscar en familia
        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="familias.nombrefamilia LIKE ?";
            }else{
                $sql.="familias.nombrefamilia LIKE ? OR ";
            }
        }

        $fam = familia::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();

        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="modelovehiculos.modelonombre LIKE ?";
            }else{
                $sql.="modelovehiculos.modelonombre LIKE ? OR ";
            }
        }

        $modelos = modelovehiculo::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();

        $aplicaciones = similar::select('id_repuestos')
                ->wherein('id_modelo_vehiculo', $modelos)
                ->get()
                ->toArray();

        $encontrados = repuesto::where('repuestos.activo',1)
            ->wherein('repuestos.id_familia', $fam)
            ->wherein('repuestos.id', $aplicaciones)
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->get();

        if($encontrados->count()>0){
            $repuestos=$this->revisar($repuestos,$encontrados);
        }



/*
        //Buscar en familia
        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="familias.nombrefamilia LIKE ?";
            }else{
                $sql.="familias.nombrefamilia LIKE ? OR ";
            }
        }

        $fam = familia::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();


        $encontrados = repuesto::where('repuestos.activo',1)
            ->wherein('repuestos.id_familia', $fam)
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);
        }

        */
        //Buscar en oem

        //Buscar en fabricantes

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                    ->distinct()
                                    ->get()
                                    ->toArray();
        $desde = 'd';
        $criterio=""; //$q;
        //if($q=="codigo_oem") $desde="o";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function bbuscar_por_descripcion($dato) //original
    {
        //usando auth en el grupo de rutas. ver web.php
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De ");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion_original=$descripcion;
        $descripcion= str_replace("-","",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=str_replace("_&_","/",$descripcion_original);
        $q = "nadita"; // Es el criterio de búsqueda discernido según el ingreso de texto del usuario en 1,2 ó 3 términos
        $numfil=0;
        $encontré=false;
        $repuestos=repuesto::where('id','fifi')->get(); //Para que devuelva un resultado vacio si no encuentra nada en ninguno de los algoritmos.
        //En este caso no es necesaria la familia para las búsquedas, entonces mas abajo (al terminar switch) se busca $fam en base a $fa
        //y al ponerle "nada de nada" no va a encontrar nada y no afectará el correr del algoritmo.
        $fa="nada de nada";
        $hay_fab=0;
        $fab_encontrado="";
        /* 16set2020: OJO: Podría mejarse si los resultados de las búsquedas se van agregando... es decir,
        primero busca por descripción y encuentra 10 resultados, (digamos res1)
        luego entra a la búsqueda por términos y en 2 términos encuentra marca_modelo otros 5 resultados... (res2)
        ENTONCES:
        resultado_total=res1+res2... y así sucesivamente...
        Entonces la pregunta sería ... en res1 tienes la colección "repuestos" y en res2 también, por lo que se sobreescribiría...
        como impedir ello y permitir agregar a la colección LARAVEL actual, la recién encontrada...

        */

        //Primero buscar en la descripción, si no hay luego en lo demás...

        $buscado=trim($descripcion);
        $buscado_original=$descripcion_original;

        //BUSQUEDA POR DESCRIPCION PRIMERO

        $sql1="";
        $repuestos=Collect();

        //Buscar cada término en descripcion
        $terminos=explode(" ",$descripcion_original);
        for($i=0;$i<count($terminos);$i++){
            $sql1.="repuestos.descripcion LIKE ? AND ";
        }
        $sql1=$sql1." repuestos.activo=?";
        $param=[];
        $busc="";
        for($i=0;$i<count($terminos);$i++){
            $busc="%".$terminos[$i]."%";
            array_push($param,$busc);
        }
        array_push($param,1);

        $encontrados = repuesto::whereRaw($sql1,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);//$this->revisar($repuestos,$encontrados);
            $q="descripcion";
        }

        if($repuestos->count()>0) $encontré=true;

        if(!$encontré){
            $d = explode(' ', $descripcion);
            //Máximo 4 términos (partes) ejm filtro aceite mazda demio
            $num_t = count($d);
            switch ($num_t)
            {
                case 1:
                    /* #region Cantidad de términos de búsqueda 1*/

                    $buscado= trim($d[0]);
                    /*
                    Como es sólo un término de búsqueda, puedo simplicar para que busque en el siguiente orden:
                    1° Por código interno (pancho repuestos) y si no encuentra que busque
                    2° Por código de fabricante (marca de repuesto) en la tabla repuestos_fabricantes y si no encuentra que busque
                    3° Por código de proveedor y si no encuentra que busque
                    4° Por código OEM y si no encuentra que busque
                    5° Por medidas
                    6° En la descripción
                    */

                    //Por familia: Creo que traería demasiados resultados y sería muy lento, pero si después quiere, lo hago.

                    //Por código interno pancho repuestos
                    $numfil=repuesto::where('codigo_interno',$buscado)
                    ->where('repuestos.activo',1)
                    ->count();

                    if($numfil>0)
                    {
                        $encontré=true;
                        $q="codigo_interno";
                    }

                    //Por código de fabricante (marca de repuesto)
                    if(!$encontré)
                    {
                        $numfil=fabricante::where('codigo_fab','LIKE','%'.$buscado.'%')
                                        ->orWhere('codigo_fab','LIKE','%'.$buscado_original.'%')
                                        ->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_fabricante";
                        }
                    }

                    $guion="-";
                    if(!$encontré)
                    {
                        $nf=strpos($buscado_original,$guion);
                        if($nf===false){ //no hay guión
                            for($i=1;$i<strlen($buscado_original);$i++){
                                $buskado = substr_replace($buscado_original, $guion, $i, 0);
                                $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                                    ->count();
                                if($numfil>0){
                                    $encontré=true;
                                    $q="codigo_fabricante";
                                    $buscado=$buskado;
                                    break;
                                }
                            }

                        }
                    }

                    if(!$encontré)
                    {

                        for($i=1;$i<strlen($buscado);$i++){
                            $buskado = substr_replace($buscado, $guion, $i, 0);
                            $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                                ->count();
                            if($numfil>0){
                                $encontré=true;
                                $q="codigo_fabricante";
                                $buscado=$buskado;
                                break;
                            }
                        }
                    }

                    //Por código de proveedor
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('cod_repuesto_proveedor', 'LIKE', $buscado_original. '%')
                        ->where('repuestos.activo',1)
                        ->count();

                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_proveedor";
                            $buscado=$buscado_original;
                        }
                    }

                    //Por código OEM
                    if(!$encontré)
                    {
                        $pos=strpos($buscado,"-");
                        if($pos===false)
                        {
                            $buscado_sin_guion=$buscado;
                            $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
                        }else{
                            $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
                            $buscado_con_guion=$buscado;
                        }

                        $numfil=oem::where('codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                                              ->orWhere('codigo_oem', 'LIKE', $buscado_con_guion. '%')
                                              ->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_oem";
                        }
                    }

                    //Por código medidas
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('repuestos.medidas', 'LIKE', $buscado. '%')
                        ->where('repuestos.activo',1)
                        ->count();

                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="medida";
                        }
                    }

                    break;
                    /* #endregion */
                case 2:
                    /* #region Cantidad de términos de búsqueda 2*/

                    //caso fam fam
                    $fa =trim($d[0]) . " " . trim($d[1]);

                    $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();

                    if ($num_f > 0) //Hay familia con todo el término buscado
                    {
                        $encontré=true;
                        $q="fam_fam";
                    }

                    //caso fam marca_veh
                    if(!$encontré)
                    {
                        $fa = trim($d[0]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        if ($num_f > 0) //encontró familia con el primer término, buscamos como marca vehiculo
                        {
                            $mav=trim($d[1]); //marca vehiculo
                            $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                            if ($num_mav > 0)
                            {
                                $encontré=true;
                                $q = "fam-marcaveh";
                            }
                        }
                    }

                    //caso fam modelo
                    if(!$encontré)
                    {
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mod = trim($d[1]); //modelo vehiculo
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f>0 && $num_mod > 0)
                        {
                            $encontré=true;
                            $q = "fam-modelo";
                        }

                    }

                    //caso fam marcarep
                    if(!$encontré)
                    {
                        $mar = trim($d[1]); //marca repuesto
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f>0 && $num_mar > 0)
                        {
                            $encontré=true;
                            $q = "fam-marcarep";
                        }
                    }

                    //caso marcaveh marcaveh
                    if(!$encontré)
                    {
                        $mav=trim($d[0])." ".trim($d[1]); //marca vehiculo
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_mav > 0)
                        {
                            $encontré=true;
                            $q = "marcaveh-marcaveh";
                        }
                    }

                    if(!$encontré) //marcaveh modeloveh
                    {
                        $mav=trim($d[0]); //marca vehiculo
                        $mod=trim($d[1]); //modelo vehiculo
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_mav > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='marca_y_modelo_veh_sin_fam';
                        }
                    }

                    if(!$encontré) //modeloveh modeloveh
                    {
                        $mod=trim($d[0])." ".trim($d[1]); //modelo vehiculo
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_mod>0)
                        {
                            $encontré=true;
                            $q='mod_mod_sin_fam';
                        }
                    }

                    if(!$encontré)
                    {
                        $mod=trim($d[0]); //modelo vehiculo
                        $mar=trim($d[1]); //marca repuesto
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_mod>0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='modeloveh_y_marcarep_sin_fam';
                        }
                    }

                    break;
                    /* #endregion */
                case 3:
                    /* #region Cantidad de términos de búsqueda 3*/

                    //caso fam fam marcaveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mav = trim($d[2]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_f > 0 && $num_mav > 0)
                        {
                            $encontré=true;
                            $q='fam_fam_marcaveh';
                        }
                    }

                    //Caso fam fam modeloveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE','%'. $fa . '%')->count();
                        $mod = trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mod > 0)
                        {
                            $encontré=true;
                            $q='fam_fam_modeloveh';
                        }
                    }


                    //caso fam fam marca_rep
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mar = trim($d[2]); //marca repuesto
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f>0 && $num_mar > 0)
                        {
                            $encontré=true;
                            $q = "fam-fam-marcarep";
                        }
                    }


                    //Caso fam marcaveh marcaveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mav = trim($d[1])." ".trim($d[2]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_f > 0 && $num_mav > 0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_marcaveh';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam marcaveh modeloveh
                        $mav = trim($d[1]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        $mod=trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mav > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_modeloveh';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam marcaveh marcarep
                        $mar=trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mav > 0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_marcarep';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam modveh modveh
                        $mod=trim($d[1])." ".trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='fam-modelo-modelo';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam modveh marcarep
                        $mod=trim($d[1]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        $mar=trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mod>0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam_modeloveh_marcarep';
                        }
                    }

                    if(!$encontré)
                    {
                        //return $fam;
                        //Fórmula: fam marcarep marcarep "fabricas chinas / valeo phc"
                        $mar=trim($d[1])." ".trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam-marcarep-marcarep';
                        }
                    }

                    break;
                    /* #endregion */
                case 4:
                    /* #region Cantidad de términos de búsqueda 4  */
                    //23ENE2020 FALTA INCLUIR BUSQUEDA POR MARCA DE VEHICULO
                    //los dos primeros es la familia y los dos últimos son  marcaveh o modeloveh o marcarep
                    $fa = trim($d[0])." ".trim($d[1]); //familia
                    $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                    if ($num_f > 0) //encontró familia con el primer término, buscamos como modelo o marca el 2do término
                    {
                        $mav = trim($d[2])." ".trim($d[3]); //marcaveh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav. '%')->count();
                        if ($num_mav > 0) {
                            $q = "marcaveh";
                         } else { //No hay por marcaveh, buscamos por modeloveh o marca de repuesto
                            $mod = trim($d[2])." ".trim($d[3]); //modelo
                            $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod. '%')->count();
                            if ($num_mod > 0) {
                                $q = "modelo";
                            } else { //No hay por modelo, buscamos por marca de repuesto
                                $mar = trim($d[2])." ".trim($d[3]); //marca repuesto
                                $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                                if ($num_mar > 0) $q = "marcarep";
                            }
                        }
                    } // si no encuentra no interesa... resultado NO HAY
                    break;
                    /* #endregion */
                default: //aquí cuando escribe más de 4 términos que busque sólo en descripción kakita
                $buscado=trim($descripcion);
                $q="descripción";

            }

            if(!$encontré){
                $buscado=trim($descripcion);
                $q="descripción";
            }

            $fam = familia::select('id')
                ->where('nombrefamilia', 'LIKE', '%'.$fa . '%')
                ->get()
                ->toArray();

            //OBTENEMOS LOS DATOS REQUERIDOS SEGÚN LAS BÚSQUEDAS PREVIAS

            /* #region buscar por código interno un término*/
            if($q=='codigo_interno')
            {
                //return $op." codi inter: ***".$buscado."***";
                if ($op == 0) {
                    //return $op." codi inter: ***".$buscado."***";
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE','%'.$buscado.'%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por fabricante (marca repuesto) un término*/
            if($q=="codigo_fabricante")
            {

                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                        ->orWhere('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }
            }


            /* #endregion */

            /* #region buscar por proveedor un término */
            if($q=='codigo_proveedor')
            {
                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado . '%')
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por oem un término */
            if($q=="codigo_oem")
            {
                $pos=strpos($buscado,"-");
                if($pos===false)
                {
                    $buscado_sin_guion=$buscado;
                    $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
                }else{
                    $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
                    $buscado_con_guion=$buscado;
                }



                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por medida un término */
            if($q=='medida')
            {
                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por familia familia 2 términos */
            if($q=="fam_fam")
            {
                $fam = familia::select('id')
                ->where('nombrefamilia', 'LIKE', '%'.$fa . '%')
                ->get()
                ->toArray();

                if ($op == 0)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por marcaveh marcaveh 2 términos */
            if ($q == 'marcaveh-marcaveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }


            /* #endregion */

                /* #region  buscar por modeloveh modeloveh sin fam 2 términos*/
                if ($q == 'mod_mod_sin_fam')
                {
                    $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod. '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

            /* #region buscar por modeloveh y marcarepuesto 2 términos*/
            if($q=='modeloveh_y_marcarep_sin_fam')
            {
                $modelosveh = modelovehiculo::select('id')
                    ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                    ->get()
                    ->toArray();

                $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_modelo_vehiculo', $modelosveh)
                    ->get()
                    ->toArray();

                $marcasrep=marcarepuesto::select('id')
                                                ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                ->get()
                                                ->toArray();

                if ($op == 0) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }
            /* #endregion */

            /* #region  buscar por marca y modelo de vehiculo sin familia 2 términos*/
            if ($q == 'marca_y_modelo_veh_sin_fam')
            {
                $marcasveh = marcavehiculo::select('idmarcavehiculo')
                    ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                    ->get()
                    ->toArray();

                $modelosveh = modelovehiculo::select('id')
                    ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                    ->get()
                    ->toArray();

                $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_marca_vehiculo', $marcasveh)
                    ->wherein('id_modelo_vehiculo', $modelosveh)
                    ->get()
                    ->toArray();

                if ($op == 0) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }
            /* #endregion */

            /* #region buscar por familia y aplicación 2 términos*/
            //BUZCAR: anillo d4bb / metal biela porter
            if($q=="fam_apli" && false)
            {

                $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                if ($op == 0 || $op==4) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_familia', $fam)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }

            /* #endregion*/

                /* #region  buscar por fam marcaveh marcaveh o fam fam marcaveh 2 y 3 términos*/
                if ($q == 'fam_marcaveh_marcaveh' || $q == 'fam_fam_marcaveh' || $q=='fam-marcaveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region  buscar por fam marca y modelo de vehiculo 3 términos*/
                if ($q == 'fam_marcaveh_modeloveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $modelosveh = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->wherein('id_modelo_vehiculo', $modelosveh)
                        ->get()
                        ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam marcaveh y marcarepuesto 3 términos*/
                if($q=='fam_marcaveh_marcarep')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam modeloveh y marcarepuesto 3 términos*/
                if($q=='fam_modeloveh_marcarep')
                {
                    $modelosveh = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelosveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region  buscar por fam modelo modelo vehiculo o fam fam modveh 2 y 3 términos*/
                if ($q == 'fam-modelo-modelo' || $q == 'fam_fam_modeloveh' || $q=='fam-modelo')
                {
                    $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod. '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam marcarep marcarep o fam fam marcarep 2 y 3 términos*/
                if($q=='fam-marcarep-marcarep' || $q=='fam-fam-marcarep' || $q=="fam-marcarep")
                {
                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

        } //fin de buscar primero solo por descripción... no encontré

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                                    ->distinct()
                                                    ->get()
                                                    ->toArray();
        $desde = 'd';
        $criterio=$q;
        if($q=="codigo_oem") $desde="o";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;

    } // fin de buscar_por_descripcion

    public function buscar_por_codigo_proveedor($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $codigo_proveedor = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'p';
        $criterio="por_codigo_proveedor";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_oem($dato)
    {
        //usando auth en el grupo de rutas. ver web.php
//SELECT repuestos.*,oems.codigo_oem FROM repuestos inner join oems on repuestos.id=oems.id_repuestos where oems.codigo_oem like '51720-29%' group by repuestos.id
        $op = substr($dato, 0, 1);
        $buscado = substr($dato, 1);
        $pos=strpos($buscado,"-");
        if($pos===false)
        {
            $buscado_sin_guion=$buscado;
            $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
        }else{
            $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
            $buscado_con_guion=$buscado;
        }
        if ($op == 0) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'o';
        $criterio="por_oem";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_codigo_fabricante($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $fab_original = substr($dato, 1);
        $fab=str_replace("-","",$fab_original);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
                                ->orWhere('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'f';
        $criterio="por_cod_fab";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_medidas($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $medidas = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_medidas";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_codigo_interno($codint)
    {



        $r = repuesto::where('repuestos.codigo_interno',$codint)
                                ->where('activo',1)
                                ->first();

        if(!is_null($r)){
            $resp=['id_repuesto'=>$r->id,'descripcion'=>$r->descripcion];
        }else{
            $resp=['id_repuesto'=>0,'descripcion'=>'NO SE ENCONTRÓ '.$codint];
        }
        return json_encode($resp);
    }

    public function buscar_por_modelo($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);

        $modelo = modelovehiculo::find($idmodelo);
        $quemodelo = 'para ' . $modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

        /*
        $debug=$quemodelo;
        $vv=view('errors.debug_ajax',compact('debug'))->render();
        return $vv;
         */

        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

        if ($op == 0) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                                    ->distinct()
                                                    ->get()
                                                    ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_modelo";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde', 'quemodelo','criterio','tienen_foto'))->render();
        return $v;
    }

    public function dame_familias_repuestos($dato)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);
        try {
            $modelo = modelovehiculo::find($idmodelo);
            $quemodelo =$modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

            if ($op == 0) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                INNER JOIN familias ON repuestos.id_familia=familias.id
                WHERE repuestos.activo=1 AND repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                GROUP BY repuestos.id_familia
                ORDER BY nombrefamilia";
            }

            if ($op == 1) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 2) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 3) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            $familias = \DB::select($s);
            $total_repuestos = 0;
            foreach ($familias as $repuesto) {
                $total_repuestos += $repuesto->total;
            }

            /*
            $debug=collect($familias)->toBase()->sum('total');
            $vv=view('errors.debug_ajax',compact('debug'))->render();
            return $vv;
             */
            //$total_repuestos=$familias->sum('total');
            $v = view('fragm.ventas_familias', compact('familias', 'dato', 'total_repuestos','quemodelo'))->render();
            return $v;

        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }

// Búsqueda de repuestos por familias y modelo
    public function dame_repuestos($id_familia, $dato)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);

        $modelo = modelovehiculo::find($idmodelo);
        $quemodelo = 'para ' . $modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

        if ($op == 0) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
        ->distinct()
        ->get()
        ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_familias_y_modelos";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde', 'quemodelo','criterio','tienen_foto'))->render();
        return $v;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('inventario.ventas_principal');
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
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
    }

    private function dame_correlativo($tip_doc)
    {
        $num=-1;
        $id_local = Session::get("local"); // es el local donde se ejecuta el terminal
        if($tip_doc=='cotizacion')
        {
            $fila = correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();
            if(!is_null($fila))
            {
                $corr=$fila->correlativo; //0
                $max_folio=$fila->hasta; //0
                if($max_folio>=($corr+1)) $num=$corr;
            }
        }else{
            $fila=correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();
            if(!is_null($fila))
            {
                $corr=$fila->correlativo;
                $max_folio=$fila->hasta;
                $el_siguiente=$corr+1;
                if($max_folio>=($el_siguiente)) $num=$corr;
                //FALTA:VERIFICAR EN LA TABLA RESPECTIVA (boletas o facturas) si hay existe ese número
                //esto debido a que A VECES en un determinado instante COINCIDE la elección del siguiente correlativo.

            }
        }
        return $num;
    }

    public function generar_xml(Request $r)
    {

        $ref1=json_decode($r->ref1);
        $ref2=json_decode($r->ref2);
        $ref3=json_decode($r->ref3);

        $referencias=[];
        if(count($ref1)>0){
            array_push($referencias,$ref1);
        }
        if(count($ref2)>0){
            array_push($referencias,$ref2);
        }
        if(count($ref3)>0){
            array_push($referencias,$ref3);
        }

        $Datos['referencias']=$referencias;

        if($r->fmapago=='contado'){
            $Datos['FmaPago']=1;
        }
        if($r->fmapago=='credito' || $r->fmapago=='delivery'){
            $Datos['FmaPago']=2;
        }

        if($r->docu=='cotizacion'){
            //FALTA: Revisar código de guardar_venta para empezar.

            return "algo";
        }


        //PREPARAMOS LOS DATOS A ENTREGAR A CLSSII

        //Correlativo
        $nume=$this->dame_correlativo($r->docu);
        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $docu=strtoupper($r->docu);
            $estado=['estado'=>'ERROR_CAF','mensaje'=>$docu.": No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++; //siguiente correlativo
            $Datos['folio_dte']=$nume;
            if($r->docu=='boleta') $Datos['tipo_dte']='39';
            if($r->docu=='factura') $Datos['tipo_dte']='33';
            if($r->docu=='notacredito') $Datos['tipo_dte']='61';
            if($r->docu=='notadebito') $Datos['tipo_dte']='56';
        }

        //Obtener cliente

        if($r->idcliente==0){ //no se eligió cliente
            $idcliente=$this->dame_cliente_sii();
        }else{
            $idcliente=$r->idcliente;
        }

        $cliente=cliente_modelo::find($idcliente);
        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);

        //$rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
        if($cliente->tipo_cliente==0){ //persona natural
            $rz=$cliente->nombres." ".$cliente->apellidos;
        }
        if($cliente->tipo_cliente==1){ //empresa
            $rz=$cliente->razon_social;
        }

        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                            'RznSocRecep'=>$rz,
                            'GiroRecep'=>$cliente->giro,
                            'DirRecep'=>$cliente->direccion,
                            'CmnaRecep'=>$cliente->direccion_comuna,
                            'CiudadRecep'=>$cliente->direccion_ciudad
                        ];

        //Obtener detalle del carrito
        $Detalle=[];
        $carrito=new carrito_compra();
        $c=$carrito->dame_todo_carrito();
        foreach($c as $i)
        {
            //$precio_neto_item=$i->pu_neto; //round($i->pu/(1+Session::get('PARAM_IVA')),0);
            if($Datos['tipo_dte']=='39'){ //boleta
                if($i->descuento_item>0){
                    $item=array('NmbItem'=>$i->descripcion,
                        'QtyItem'=>$i->cantidad,
                        'PrcItem'=>round($i->pu,2),//intval($i->pu),
                        'DescuentoMonto'=>round($i->descuento_item,2) //intval($i->descuento_item)
                    );
                }else{
                    $item=array('NmbItem'=>$i->descripcion,
                        'QtyItem'=>$i->cantidad,
                        'PrcItem'=>round($i->pu,2)
                    );
                }
            }else{ // factura
                if($i->descuento_item>0){
                    $item=array('NmbItem'=>$i->descripcion,
                                'QtyItem'=>$i->cantidad,
                                'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                'DescuentoMonto'=>round($i->descuento_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                );
                }else{
                    $item=array('NmbItem'=>$i->descripcion,
                                'QtyItem'=>$i->cantidad,
                                'PrcItem'=>round($i->pu,2),//$i->pu_neto //ya llega redondeado desde el carrito
                                );
                }
            }

            array_push($Detalle,$item);
        }
        $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array

        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre',$r->docu);
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente',$idcliente);
            $this->actualizar_correlativo($r->docu, $nume);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
        }

        return json_encode($estado);
    }

    public function limpiar_sesion(){
        Session::put('xml',0);
        Session::put('tipo_dte',0);
        Session::put('tipo_dte_nombre','');
        Session::put('folio_dte',0);
        Session::put('idcliente',0);
    }

    public function set_xml_imprimir($xml){
        Session::put('xml',$xml);
    }

    public function enviar_sii(Request $r)
    {
        $id_cliente=$r->idcliente==0 ? $this->dame_cliente_sii() : $r->idcliente; //para guardar la boleta y factura

        if(Session::get('xml')==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT_ENVIA'));
        $RutEmisor = str_replace(".","",Session::get('PARAM_RUT'));
        $d=Session::get('xml');
        $tipo_dte=Session::get('tipo_dte');
        if($tipo_dte=='39')
        {
            $doc=base_path().'/xml/generados/boletas/'.$d;
        }
        if($tipo_dte=='33')
        {
            $doc=base_path().'/xml/generados/facturas/'.$d;
        }
        if($tipo_dte=='61')
        {
            $doc=base_path().'/xml/generados/notas_de_credito/'.$d;
        }
        if($tipo_dte=='56')
        {
            $doc=base_path().'/xml/generados/notas_de_debito/'.$d;
        }

        $tipo_docu="nada";
        $num_docu=0;
        $id_documento_pago = 0;
       //Recuperar el XML Generado para enviar
        try {
            $envio=file_get_contents($doc);
            if($tipo_dte=='39'){
                $rs=ClsSii::enviar_sii_boleta($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                }else{
                    return json_encode($rs);
                }

                $b = new boleta;
                $b->num_boleta = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $b->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $b->es_credito=$r->venta=="credito" ? 1 : 0;
                $b->es_delivery=$r->venta=="delivery" ? 1 : 0;
                $b->id_cliente = $id_cliente;
                $b->estado = $estado;
                $b->estado_sii=$estado_sii;
                $b->resultado_envio=$resultado_envio;
                $b->trackid=$TrackID;
                $b->url_xml=$d;
                $b->total = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);// round(intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal)*(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP); //incluye el iva
                $b->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);//intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
                $b->exento = 0;
                $b->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA); //round($b->neto*Session::get('PARAM_IVA'),0,PHP_ROUND_HALF_UP);
                $b->activo = 1;
                $b->usuarios_id = Session::get('usuario_id');
                $b->save();
                //hay que sacar lo que falta, pero el tema de montos sacarlos del XML enviado
                $carrito=new carrito_compra();
                $c = $carrito->dame_todo_carrito();
                foreach($c as $i){
                    $bd = new boleta_detalle;
                    $bd->id_boleta = $b->id;
                    $bd->id_repuestos = $i->id_repuestos;
                    $bd->id_unidad_venta = $i->id_unidad_venta;
                    $bd->id_local = $i->id_local;
                    $bd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem);//*(1+Session::get('PARAM_IVA')); //$i->pu;
                    $bd->pu_neto = round($bd->precio_venta/(1+Session::get('PARAM_IVA')),2);
                    $bd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                    $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem);//*(1+Session::get('PARAM_IVA'));
                    $bd->subtotal = $sb;//$i->subtotal_item;
                    $bd->descuento = $i->descuento_item;
                    $bd->total = $sb-$i->descuento_item;
                    $bd->activo = 1;
                    $bd->usuarios_id = Session::get('usuario_id');
                    $bd->save();
                }

                $tipo_docu="boleta";
                $num_docu=$b->num_boleta;
                $id_documento_pago = $b->id;
            } // fin DE BOLETA

            if($tipo_dte=='33'){ //FACTURA
                $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];
                }else{
                    return json_encode($rs);
                    //$TrackID="---";
                    //$estado_sii=$rs['estado'];
                }
                $f = new factura;
                $f->num_factura = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $f->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $f->es_credito=$r->venta=="credito" ? 1 : 0;
                $f->es_delivery=$r->venta=="delivery" ? 1 : 0;
                $f->id_cliente = $id_cliente;
                $f->estado = $estado;
                $f->estado_sii=$estado_sii;
                $f->resultado_envio=$resultado_envio;
                $f->trackid=$TrackID;
                $f->url_xml=$d;
                $f->total =intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
                $f->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
                $f->exento = 0;
                $f->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
                $f->activo = 1;
                $f->usuarios_id = Session::get('usuario_id');
                $f->save();

                //hay que sacar lo que falta, pero el tema de montos sacarlos del XML enviado
                $carrito=new carrito_compra();
                $c = $carrito->dame_todo_carrito();
                foreach($c as $i){
                    $fd = new factura_detalle;
                    $fd->id_factura = $f->id;
                    $fd->id_repuestos = $i->id_repuestos;
                    $fd->id_unidad_venta = $i->id_unidad_venta;
                    $fd->id_local = $i->id_local;
                    $fd->precio_venta = $xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem;//round($fd->pu_neto*(1+Session::get('PARAM_IVA')),2,PHP_ROUND_HALF_UP);
                    $fd->pu_neto = round($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem/(1+Session::get('PARAM_IVA')),2);
                    $fd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                    $fd->subtotal = $fd->precio_venta*$fd->cantidad;
                    //if hay descuento en el xml?? poner, sino 0;
                    if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                        $fd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                    }else{
                        $fd->descuento=0;
                    }

                    $fd->total = $fd->subtotal-$fd->descuento;
                    $fd->activo = 1;
                    $fd->usuarios_id = Session::get('usuario_id');
                    $fd->save();
                }
                $tipo_docu="factura";
                $num_docu=$f->num_factura;
                $id_documento_pago = $f->id;
            } // fin de FACTURA


            //Guardar detalle del multi pago
            //forma_pago,monto,referencia



            if($r->venta=='contado'){
                //FALTA: VERIFICAR SI EL PAGO YA EXISTE Y PONERLO EN LA FECHA INDICADA.
                //EL CASO ES DEL CAMBIO DE UNA BOLETA DE AYER POR UNA FACTURA HOY QUE SE HACE MEDIANTE NOTA DE CREDITO
                //Y NO DUPLICAR EL PAGO SOBRE TODO LOS DE TRANSBANK...

                for ($i = 0; $i < count($r->forma_pago); $i++) {
                    $p = new pago;
                    $p->tipo_doc = substr($r->docu, 0, 2); //factura, boleta
                    $p->id_doc = $id_documento_pago; // Es el id del documento factura o boleta guardado más arriba
                    $p->id_cliente = $id_cliente;
                    $p->id_forma_pago = $r->forma_pago[$i];
                    $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha de hoy
                    $p->monto = $r->monto[$i];
                    $p->referencia = $r->referencia[$i];
                    $p->activo = 1;
                    $p->usuarios_id = Session::get('usuario_id');
                    $p->save();
                }
            }

            /*
                Este fragmento de código es similar en clientes_controlador método agregacuenta
            */
            if($r->venta=='credito' || $r->venta=='delivery'){ //poner en la cuenta del cliente
                $cuenta=new cliente_cuenta;
                $cuenta->id_cliente=$id_cliente;
                $cuenta->fecha_operacion=Carbon::today()->toDateString(); //Solo la fecha;

                if($tipo_dte=='39'){
                    $total_deuda=$b->total;
                    $referencia_deuda="Boleta N° ".$b->num_boleta;
                }
                if($tipo_dte=='33'){
                    $total_deuda=$f->total;
                    $referencia_deuda="Factura N° ".$f->num_factura;
                }
                if($r->venta=='delivery'){
                    $referencia_deuda.=" %Delivery";
                }
                //es deuda
                $cuenta->pago=0;
                $cuenta->deuda=$total_deuda;

                $cuenta->referencia=$referencia_deuda;
                $cuenta->activo=1;
                $cuenta->usuarios_id=Session::get("usuario_id");
                $cuenta->save();
            }

        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
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

    public function cotizar(Request $r){
        try{
            $num=$this->dame_correlativo($r->docu) + 1;
            if($num<=0){
                $estado=['estado'=>'ERROR','mensaje'=>'No hay correlativos disponibles'];
                return json_encode($estado);
            }
            $carrito=new carrito_compra();
            $c = new cotizacion;
            $c->num_cotizacion = $num;
            $c->nombre_cotizacion=$r->nombre_cotizacion;
            $c->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
            $c->fecha_expira = Carbon::today()->addDays(7)->toDateString();
            $c->id_cliente = $r->idcliente;
            $c->total = $carrito->dame_total(); //incluye el iva
            $c->neto = $carrito->dame_neto();
            $c->iva = $carrito->dame_iva();

            $c->activo = 1;
            $c->usuarios_id = Session::get('usuario_id');
            $c->save();

            $items=$carrito->dame_todo_carrito();
            foreach ($items as $i) {
                $cd = new cotizacion_detalle;
                $cd->id_cotizacion = $c->id;
                $cd->id_repuestos = $i->id_repuestos;
                $cd->id_unidad_venta = $i->id_unidad_venta;
                $cd->id_local=$i->id_local;
                $cd->precio_venta = $i->pu;
                $cd->cantidad = $i->cantidad;
                $cd->subtotal = $i->subtotal_item;
                $cd->descuento = $i->descuento_item;
                $cd->total = $i->total_item;
                $cd->activo = 1;
                $cd->usuarios_id = Session::get('usuario_id');
                $cd->save();

            } //Fin bucle carrito compras

            $num_docu = $c->num_cotizacion;
            $this->actualizar_correlativo($r->docu, $num_docu);
            $estado=['estado'=>'OK','cotizacion'=>$num_docu];
            return json_encode($estado);
        } catch (\Exception $error) {
            $estado=['estado'=>'ERRORRR','mensaje'=>$error->getMessage()];
            return json_encode($estado);

        }
    }

    public function guardar_venta(Request $r){
        //usando auth en el grupo de rutas. ver web.php
        $carrito=new carrito_compra();
        $retornar_id = "0";
        $num_docu = 0;
        $id_cliente=$r->idcliente;
        if($id_cliente==0) //No se ha elegido cliente para cotización o boleta
        {
            $id_cliente=$this->dame_cliente_0();
            if($id_cliente<0)
            {
                $msje="eEn la tabla clientes no se ha definido el cliente 0000000000";
                return $msje;
            }
        }

        try {
            if ($r->docu == "cotizacion") //No hay forma de pago
            {
                //llega idcliente y docu q es cotizacion
                $c = new cotizacion;
                $c->num_cotizacion = $this->dame_correlativo($r->docu) + 1;
                $c->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                $c->fecha_expira = Carbon::today()->addDays($r->dias_expira)->toDateString();
                $c->id_cliente = $id_cliente;
                $c->total = $carrito->dame_total(); //incluye el iva
                $c->neto = $carrito->dame_neto();
                $c->iva = $carrito->dame_iva();

                $c->activo = 1;
                $c->usuarios_id = Session::get('usuario_id');
                $c->save();

                //Guarda detalle de cotización
                $cc = $carrito->dame_todo_carrito();
                foreach ($cc as $i) {
                    $cd = new cotizacion_detalle;
                    $cd->id_cotizacion = $c->id;
                    $cd->id_repuestos = $i->id_repuestos;
                    $cd->id_unidad_venta = $i->id_unidad_venta;
                    $cd->id_local=$i->id_local;
                    $cd->precio_venta = $i->pu;
                    $cd->cantidad = $i->cantidad;
                    $cd->subtotal = $i->subtotal_item;
                    $cd->descuento = $i->descuento_item;
                    $cd->total = $i->total_item;
                    $cd->activo = 1;
                    $cd->usuarios_id = Session::get('usuario_id');
                    $cd->save();

                } //Fin bucle carrito compras

                $num_docu = $c->num_cotizacion;
                $retornar_id = "co&" . $c->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');

            } else { //venta factura o boleta
                $id_documento_pago = 0;
                if ($r->docu == "factura") //venta con factura
                {
                    //último correlativo utilizado
                    $nume=$this->dame_correlativo($r->docu);
                    if($nume<0) //Se acabó el correlativo autorizado por SII
                    {
                        return "eFACTURA:No hay correlativo autorizado por SII. Descargar nuevo CAF";
                    }else{
                        $nume++; //siguiente correlativo
                    }

                    //Envío al SII, retorna un JSON
                    $ref['id_cliente']=$id_cliente;
                    $rpta_sii=ClsSii::enviar_documento($r->docu,$nume,$ref);
                    $rs=json_decode($rpta_sii,true); //el true convierte en array asociativo... IMPORTANTE...
                    if($rs['estado']!="ACEPTADO")
                    {
                        return "e".$rs['estado'].": ".$rs['mensaje'];
                    }

                    $f = new factura;
                    $f->num_factura = $nume;
                    $f->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                    $f->id_cliente = $id_cliente;
                    $f->docum_referencia = "---";
                    $f->total = $carrito->dame_total(); //incluye el iva
                    $f->neto = $carrito->dame_neto();
                    $f->exento = 0;
                    $f->iva = $carrito->dame_iva();

                    $f->activo = 1;
                    $f->usuarios_id = Session::get('usuario_id');
                    $f->save();
                    $id_documento_pago = $f->id;

                    //Guarda detalle de factura, abrir carrito compras
                    $c = $carrito->dame_todo_carrito();
                    foreach ($c as $i) {
                        $fd = new factura_detalle;
                        $fd->id_factura = $f->id;
                        $fd->id_repuestos = $i->id_repuestos;
                        $fd->id_unidad_venta = $i->id_unidad_venta;
                        $fd->id_local = $i->id_local;
                        $fd->precio_venta = $i->pu;
                        $fd->cantidad = $i->cantidad;
                        $fd->subtotal = $i->subtotal_item;
                        $fd->descuento = $i->descuento_item;
                        $fd->total = $i->total_item;
                        $fd->activo = 1;
                        $fd->usuarios_id = Session::get('usuario_id');
                        $fd->save();

                        //Actualizar saldos en repuestos
                        //tabla SALDOS(id_repuestos,id_local,saldo)
                        //tabla REPUESTOS(id,stock_actual)
                        //en repuestocontrolador método público actualiza_saldos(operacion,idrep,idlocal,cantidad)
                        $rc = new repuestocontrolador();
                        $rc->actualiza_saldos("E", $i->id_repuestos, $i->id_local, $i->cantidad);

                    } //Fin bucle carrito compras

                    $num_docu = $f->num_factura;
                    $retornar_id = "fa&" . $f->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');

                } else { // venta con boleta
                    if ($r->docu == "boleta") //venta con boleta
                    {
                        //Guarda cabecera de boleta
                        $nume=$this->dame_correlativo($r->docu);
                        if($nume<0) //Se acabó el correlativo autorizado por SII
                        {
                            return "eBOLETA:No hay correlativo autorizado por SII. Descargar nuevo CAF";
                        }else{
                            $nume++; //siguiente correlativo
                        }

                        $ref['id_cliente']=$id_cliente;
                        $rpta_sii=ClsSii::enviar_documento($r->docu,$nume,$ref);
                        $rs=json_decode($rpta_sii,true); //el true convierte en array asociativo... IMPORTANTE...

                        if($rs['estado']!="ACEPTADO")
                        {
                            switch ($rs['estado'])
                            {
                                case 'SIN_CORREO':
                                case 'ERROR_MAIL':
                                case 'ERROR_CAF':
                                case 'ERROR_TIMBRAR':
                                case 'ERROR_CERTIFICADO':
                                case 'ERROR_FIRMA_DTE':
                                case 'ERROR_AGREGAR_DTE':
                                case 'ERROR_CARATULA':
                                case 'ERROR_GENERAR_ENVIO_DTE':
                                case 'ERROR_TOKEN':
                                case 'ERROR_GUARDAR_XML':
                                case 'ERROR_ENVIO_SII': // Sii::enviar(...)
                                case 'ERROR_STATUS':
                                case 'ERROR_GET_ESTADO':
                                case 'ERROR_ESTADO_UPLOAD':
                                case 'ERROR_FATAL': // Exception
                                case 'ERROR_INDEFINIDO':
                                    return "e".$rs['estado'].": ".$rs['mensaje'];
                                break;
                                case 'ERROR_NO_EPR':
                                case 'ERROR_RECHAZADO':
                                case 'ERROR_REPARO':
                                    //guardar cabecera del documento especificando el error y el trackID si hay
                                    $b = new boleta;
                                    $b->num_boleta = $nume;
                                    $b->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                                    $b->id_cliente = $id_cliente;
                                    $b->estado = 0; //0.- que no es aceptado por SII. 1.- Es aceptado por SII
                                    $b->estado_sii=$rs['estado'];
                                    $b->trackid=$rs['trackid'];
                                    $b->url_xml=$rs['xml'];
                                    $b->total = 0;//$carrito->dame_total(); //incluye el iva
                                    $b->neto = 0; //$carrito->dame_neto();
                                    $b->exento = 0;
                                    $b->iva = 0; //$carrito->dame_iva();
                                    $b->activo = 1;
                                    $b->usuarios_id = Session::get('usuario_id');
                                    $b->save();
                                    $num_docu = $b->num_boleta;

                                    //guardar correlativo del documento
                                    $this->actualizar_correlativo($r->docu, $num_docu);
                                    return "e".$rs['estado'].": ".$rs['mensaje'];
                                break;
                                default:
                                    return "eError No conteplando en ClsSii::enviar_documento(...)";
                            }


                        }

                        //cabecera normal aceptada sin errores
                        $b = new boleta;
                        $b->num_boleta = $nume;
                        $b->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                        $b->id_cliente = $id_cliente;
                        $b->estado = 1;
                        $b->estado_sii=$rs['estado'];
                        $b->trackid=$rs['trackid'];
                        $b->url_xml=$rs['xml'];
                        $b->total = $carrito->dame_total(); //incluye el iva
                        $b->neto = $carrito->dame_neto();
                        $b->exento = 0;
                        $b->iva = $carrito->dame_iva();
                        $b->activo = 1;
                        $b->usuarios_id = Session::get('usuario_id');
                        $b->save();
                        $id_documento_pago = $b->id;

                        //Guarda detalle de boleta, abrir carrito compras
                        $c = $carrito->dame_todo_carrito();
                        foreach ($c as $i) {
                            $bd = new boleta_detalle;
                            $bd->id_boleta = $b->id;
                            $bd->id_repuestos = $i->id_repuestos;
                            $bd->id_unidad_venta = $i->id_unidad_venta;
                            $bd->id_local = $i->id_local;
                            $bd->precio_venta = $i->pu;
                            $bd->cantidad = $i->cantidad;
                            $bd->subtotal = $i->subtotal_item;
                            $bd->descuento = $i->descuento_item;
                            $bd->total = $i->total_item;
                            $bd->activo = 1;
                            $bd->usuarios_id = Session::get('usuario_id');
                            $bd->save();

                            //Actualizar saldos en repuestos
                            //tabla SALDOS(id_repuestos,id_local,saldo)
                            //tabla REPUESTOS(id,stock_actual)
                            //en  repuestocontrolador método público actualiza_saldos(operacion,idrep,idlocal,cantidad)
                            $rc = new repuestocontrolador();
                            $rc->actualiza_saldos("E", $i->id_repuestos, $i->id_local, $i->cantidad);

                        } //Fin bucle carrito compras

                        $num_docu = $b->num_boleta;
                        $retornar_id = "bo&" . $b->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');
                    }
                } // fin venta boleta

                if ($r->venta == "contado") // a crédito no hay queforma de pago
                {
                    //Guardar detalle del multi pago
                    //forma_pago,monto,referencia
                    for ($i = 0; $i < count($r->forma_pago); $i++) {
                        $p = new pago;
                        $p->tipo_doc = substr($r->docu, 0, 2); //factura, boleta, cotizacion
                        $p->id_doc = $id_documento_pago;
                        $p->id_cliente = $id_cliente;
                        $p->id_forma_pago = $r->forma_pago[$i];
                        $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha
                        $p->referencia = $r->referencia[$i];
                        $p->monto = $r->monto[$i];
                        $p->activo = 1;
                        $p->usuarios_id = Session::get('usuario_id');
                        $p->save();
                    }
                }
            }

            $this->actualizar_correlativo($r->docu, $num_docu);
            $referencia="---"; //para boletas y facturas
            return $retornar_id."&".$id_cliente."&".$referencia;
        } catch (\Exception $error) {
            $err="e".$error->getMessage();
            return $err;
        }

    }

    private function actualizar_correlativo($docu, $num)
    {
        $co = correlativo::where('documento', $docu)
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $s=$co->save();
    }

    private function dameultimoitem($id_usu)
    {

        $ultimo = carrito_compra::where('usuarios_id', $id_usu)->max('item');//carrito_compra::where('usuarios_id', $id_usu)->latest()->value('item');
        if (is_null($ultimo)) {
            $ultimo = 0;
        }
        return $ultimo;
    }

    private function re_enumera_items_carrito()
    {
        $car=new carrito_compra();
        $carrito=$car->dame_todo_carrito();
        $c=1;
        foreach($carrito as $item){
            $item->item=$c;
            $item->save();
            $c++;
        }

    }

    public function borrar_item_carrito($id)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        carrito_compra::destroy($id);
        $this->re_enumera_items_carrito();
        return $this->dametotalcarrito();
    }

    private function dametotalcarrito()
    {
        //usando auth en el grupo de rutas. ver web.php
        $total = (new carrito_compra)->dame_total(Session::get('usuario_id'));
        return $total;
    }

    public function agregar_carrito(Request $r)
    {
        //llega idrep y cantidad desde function agregar_carrito(id_rep) en ventas_principal.blade
        //usando auth en el grupo de rutas. ver web.php //Valida sesión

        try {
            //Buscar si artículo ya esta en el carrito
            $esta = carrito_compra::where('id_repuestos', $r->idrep)->first();
            if (!is_null($esta)) {
                return "existe";
            }

            $repuesto = repuesto::find($r->idrep);
            $c = new carrito_compra;
            $c->usuarios_id = Session::get('usuario_id');
            $c->item = $this->dameultimoitem(Session::get('usuario_id')) + 1;
            $c->id_repuestos = $r->idrep;
            $c->id_local = $r->idlocal;
            $c->id_unidad_venta = $repuesto->id_unidad_venta;
            $c->cantidad = $r->cantidad;
            $c->pu = $repuesto->precio_venta; //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR
            $c->pu_neto=round($c->pu/(1+Session::get('PARAM_IVA')),2);
            $c->descuento_item = 0.00;
            $c->subtotal_item = $c->cantidad * $c->pu;
            $c->total_item = $c->subtotal_item - $c->descuento_item;
            $c->save();

            if ($r->idcliente != 0) //Se eligió cliente
            {
                $this->descuentos_carrito($r->idcliente);
            }
            $this->re_enumera_items_carrito();
            return $this->dametotalcarrito();
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }

    }

    public function cambiar_precio_item_carrito($dato)
    {
        //06jun2020 por ahora no lo utilizo
        //Cuando se modifica un precio (en ventas->buscar->M) si se modifica
        //un repuesto debería modificar el precio del item del carrito si esta agregado.
        $rpta="XUXA";
        try{
            $d=explode("&",$dato);
            $idrep=$d[0];
            $nuevo_precio=$d[1];
            $idcliente=$d[2];
            //a todos los carritos pendientes...
            $cc=carrito_compra::where('id_repuestos',$idrep)->first();
            if(!is_null($cc))
            {
                $cc->pu=$nuevo_precio;
                $cc->subtotal_item=$cc->cantidad*$cc->pu;
                $cc->total_item=$cc->subtotal_item-$cc->descuento_item;
                $cc->save();
                //puxa... aqui revisar si el cliente tiene descuento para aplicarle...
                // XUXHE SU MAEEE...
                if ($r->idcliente != 0) //Se eligió cliente
                {
                    $this->descuentos_carrito($idcliente);
                }

                $rpta="OK";
            }else{
                $rpta="NO EXISTE";
            }

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
        return $rpta;
    }

    public function verificar_nombre_carrito($el_nombre_carrito)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $el_id_usuario=Session::get('usuario_id');

        //Verificar que el carro no existe por nombre...
        $car=carrito_guardado::where('nombre_carrito',$el_nombre_carrito)
                                            ->where('usuarios_id',$el_id_usuario)
                                            ->first();
        if(!is_null($car))
        {
            return "existe";
        }else{
            $responde=$this->guardar_carrito_completo($el_nombre_carrito,"NO");
            return $responde;
        }
    }

    public function guardar_carrito_completo($el_nombre_carrito,$existe)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión

        $el_id_usuario=Session::get('usuario_id');




        //Si existe el carrito, borrarlo porque confirmó que desea reemplazarlo en inventario.ventas_principal.blade.php (verificar_nombre_carrito())
        if($existe=="SI")
        {
            $borrados=carrito_guardado::where('nombre_carrito',$el_nombre_carrito)
                                    ->where('usuarios_id',$el_id_usuario)
                                    ->delete();
        }else{
            //NOTA: Guardar como máximo 5 carritos
            $cuantos=carrito_guardado::select('nombre_carrito')
                ->where('usuarios_id',$el_id_usuario)
                ->distinct()
                ->get()
                ->count();
            if($cuantos==5)
            {
                return "Solo se puede guardar 5 carritos como máximo.";
            }
        }

        //bucle del carrito activo
        $carrito_compra = carrito_compra::where('usuarios_id', $el_id_usuario)->get();
        foreach($carrito_compra as $cc)
        {
            $cg=new carrito_guardado;
            $cg->nombre_carrito=$el_nombre_carrito;
            $cg->usuarios_id = $el_id_usuario;
            $cg->item=$cc->item;
            $cg->id_repuestos=$cc->id_repuestos;
            $cg->id_local=$cc->id_local;
            $cg->id_unidad_venta=$cc->id_unidad_venta;
            $cg->cantidad=$cc->cantidad;
            $cg->pu=$cc->pu;
            $cg->subtotal_item=$cc->subtotal_item;
            $cg->descuento_item=$cc->descuento_item;
            $cg->total_item=$cc->total_item;
            $cg->save();
        }
        return $el_nombre_carrito;
    }

    public function cargar_carrito_completo($nombre_carrito)
    {
        $el_id_usuario=Session::get('usuario_id');
        //usar nombre del carrito (quien) y el id del usuario
        //borrar el carrito activo
        $this->borrar_carrito('actual');
        //copiar el carrito guardado hacia el carrito activo
        $guardado=carrito_guardado::where('usuarios_id',$el_id_usuario)
                                            ->where('nombre_carrito',$nombre_carrito)
                                            ->get();
        foreach($guardado as $cg)
        {
            $cc=new carrito_compra;
            $cc->usuarios_id=$el_id_usuario;
            $cc->item=$cg->item;
            $cc->id_repuestos=$cg->id_repuestos;
            $cc->id_local=$cg->id_local;
            $cc->id_unidad_venta=$cg->id_unidad_venta;
            $cc->cantidad=$cg->cantidad;
            $cc->pu=$cg->pu;
            $cc->subtotal_item=$cg->subtotal_item;
            $cc->descuento_item=$cg->descuento_item;
            $cc->total_item=$cg->total_item;
            $cc->save();
        }
        return "OK";
    }

    public function dame_carritos_guardados()
    {
        //usando auth en el grupo de rutas. ver web.php
        $el_id_usuario=Session::get('usuario_id');
        $cgs=carrito_guardado::select('nombre_carrito')
                                        ->where('usuarios_id',$el_id_usuario)
                                        ->distinct()
                                        ->get()
                                        ->toJson();
        return $cgs;
    }

    public function borrar_carrito($cual)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $el_id_usuario=Session::get('usuario_id');
        if($cual=="actual")
        {
            $borrados=carrito_compra::where('usuarios_id',$el_id_usuario)
            ->delete();
        }
        if($cual=="guardados")
        {
            $borrados=carrito_guardado::where('usuarios_id',$el_id_usuario)
            ->delete();
        }
        return $cual;

    }

    public function descuentos_carrito($id_cliente)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        if($id_cliente==0){
            $tipo_descuento=0;
        }else{
            $c = cliente_modelo::select('tipo_descuento', 'porcentaje')
                ->where('id', $id_cliente)
                ->first();
            if(is_null($c)){
                $tipo_descuento=0;
            }else{
                $tipo_descuento=$c->tipo_descuento;
            }
        }


        $cc = carrito_compra::where('usuarios_id', Session::get('usuario_id'))->get();

        if ($tipo_descuento == 0) // Sin Descuento
        {
            foreach ($cc as $item) {
                $item->descuento_item = 0;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }

        }

        if ($tipo_descuento == 1) //Descuento simple
        {
            //Que le aplique el porcentaje a cada item para que quede grabado y devuelva todo el carrito modificado.
            //Estoy buscando hacer "UPDATE carrito_compra set descuento_item=subtotal_item*porcentaje WHERE condicion"
            //Pero no se como hacerlo en laravel, como obtengo el valor subtotal_item en la misma query.
            //Asi que por ahora un bucle no mas... (21set2019)

            foreach ($cc as $item) {
                $item->descuento_item = $item->subtotal_item * $c->porcentaje / 100;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }

        }

        if ($tipo_descuento == 3) //Descuento por familia
        {
            //carrito_compras.id_repuestos / repuestos.id_familia / descuentos.id_familia, descuentos.porcentaje, descuentos.id_cliente

            foreach ($cc as $item) {
                $idfam = repuesto::where('id', $item->id_repuestos)->where('repuestos.activo',1)->value('id_familia');
                $porcentaje = descuento::where('id_cliente', $id_cliente)
                    ->where('id_familia', $idfam)
                    ->value('porcentaje');
                if (is_null($porcentaje)) {
                    $porcentaje = 0;
                }
                //No tiene descuento por familia
                $item->descuento_item = $item->subtotal_item * $porcentaje / 100;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }
        }
        return $id_cliente;

    }

    public function dame_formas_pago()
    {
        //usando auth en el grupo de rutas. ver web.php
        $formas = formapago::all();
        $v = view('fragm.dame_formas_pago', compact('formas'))->render();
        return $v;
    }

    public function dame_formas_pago_delivery()
    {
        /* en la tabla formapago los registros son
            1. Efectivo
            2. Tarjeta Crédito
            3. Cheque
            4. Transferencia Banco
            5. Tarjeta Débito
            6. Otra
        */

        $fpd=[1,4];
        $formas = formapago::wherein('id',$fpd)->get();
        $v = view('fragm.dame_formas_pago_delivery', compact('formas'))->render();
        return $v;
    }

    public function dame_formas_pago_modificar_pagos(){
        $formas = formapago::where('activo',1)->get();
        return json_encode($formas);
    }

    public function cargar_pago($id){
        $pago=pago::find($id);
        return json_encode($pago);
    }

    public function actualizar_pago(Request $r){
        try {
            pago::where('id',$r->id_pago_actualizar)
                ->update(['id_forma_pago'=>$r->id_forma_pago,'fecha_pago'=>$r->fecha_pago,'referencia'=>$r->referencia_pago,'activo'=>$r->activo_pago]);
            $rpta=["estado"=>'OK'];
        } catch (\Exception $e) {
            $rpta=["estado"=>'ERROR','mensaje'=>$e->getMessage()];
        }
        return json_encode($rpta);
    }

    public function agregar_pago(Request $r){

        $fecha_pago=$r->fecha_pago;
        list($tipo_doc,$id_doc,$num_doc,$total_doc,$id_cliente)=explode("_",$r->dato);
        if($tipo_doc=='33') $tipo_doc="fa";
        if($tipo_doc=='39') $tipo_doc="bo";

        try {
            for ($i = 0; $i < count($r->id_forma_pago); $i++) {
                $p = new pago;
                $p->tipo_doc = $tipo_doc; //factura, boleta
                $p->id_doc = $id_doc; // Es el id del documento factura o boleta guardado más arriba
                $p->id_cliente = $id_cliente;
                $p->id_forma_pago = $r->id_forma_pago[$i];
                $p->fecha_pago = $fecha_pago;
                $p->monto = $r->monto_forma_pago[$i];
                $p->referencia = $r->referencia_forma_pago[$i];
                $p->activo = 1;
                $p->usuarios_id = Session::get('usuario_id');
                $p->save();
            }
            //cambiar el estado de delivery a 2 según el documento
            if($tipo_doc=="bo"){
                $doc=boleta::find($id_doc);
            }else{
                $doc=factura::find($id_doc);
            }
            $doc->es_delivery=2; //Significa delivery pagado.
            $doc->save();
            return "OK";
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function dame_carrito_vista()
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $car=new carrito_compra;
        $carrito=$car->dame_todo_carrito();
        $total=$car->dame_total();
        $v = view('fragm.ventas_carrito', compact('carrito', 'total'))->render();
        return $v;
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

    private function dame_cliente_sii()
    {
        $rpta=-1; //No esta definido el rut del sii
        $c0=cliente_modelo::where('rut','60803000K')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    public function dame_cotizaciones($id_cliente)
    {
        //usando auth en el grupo de rutas. ver web.php
        if($id_cliente==0) //En ventas no se ha elegido cliente
        {
            $id_cliente=$this->dame_cliente_0(); //Buscamos en la tabla clientes el ID del cliente NO ELEGIDO.
            if($id_cliente<0) //No se ha definido el cliente 000000000 que significa cliente NO ELEGIDO.
                return $id_cliente;
        }
        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 30 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_30=$hoy->subDays(30);
        $borrados1=cotizacion::where('created_at','<=',$fecha_30)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<=',$fecha_30)
                                            ->delete();

        $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, IF(clientes.tipo_cliente=0,CONCAT(clientes.nombres,\' \',clientes.apellidos),clientes.razon_social) as elcliente'))
                                ->join('clientes','cotizaciones.id_cliente','clientes.id')
                                ->where('id_cliente',$id_cliente)
                                ->where('fecha_expira','>=',$fecha_hoy)
                                ->where('fecha_emision','<=',$fecha_hoy)
                                ->orderBy('fecha_emision','DESC')
                                ->get()->toJson();
        return $cot;
    }

    public function dame_cotizaciones_mes($datos)
    {
        list($nombre,$id_cliente)=explode("&",$datos);
        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 30 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_30=$hoy->subDays(30);
        $borrados1=cotizacion::where('created_at','<',$fecha_30)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<',$fecha_30)
                                            ->delete();

        if($id_cliente==0){
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, \'Ninguno\' as elcliente'))
                                ->where('cotizaciones.nombre_cotizacion','LIKE','%'.$nombre.'%')
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->orderBy('cotizaciones.fecha_emision','DESC')
                                ->get();
        }else{
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, IF(clientes.tipo_cliente=0,CONCAT(clientes.nombres,\' \',clientes.apellidos),clientes.razon_social) as elcliente'))
                                ->join('clientes','cotizaciones.id_cliente','clientes.id')
                                ->where('cotizaciones.nombre_cotizacion','LIKE','%'.$nombre.'%')
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->orderBy('cotizaciones.fecha_emision','DESC')
                                ->get();
        }
        return $cot->toJson();
    }

    public function cargar_cotizacion($num_cotizacion)
    {
        $el_id_usuario=Session::get('usuario_id');
        try {
            //borrar el carrito activo
            $this->borrar_carrito('actual');
            //copiar el carrito guardado hacia el carrito activo
            $id_cotizacion=cotizacion::where('num_cotizacion',$num_cotizacion)
                                    ->value('id');

            $cotizacion=cotizacion_detalle::where('id_cotizacion',$id_cotizacion)
                                            ->orderBy('id','ASC')
                                            ->get();
            $item=1;
            if($cotizacion->count()>0){
                foreach($cotizacion as $cot)
                {
                    $cc=new carrito_compra;
                    $cc->usuarios_id=$el_id_usuario;
                    $cc->item=$item;
                    $cc->id_repuestos=$cot->id_repuestos;
                    $cc->id_local=$cot->id_local;
                    $cc->id_unidad_venta=$cot->id_unidad_venta;
                    $cc->cantidad=$cot->cantidad;
                    $cc->pu=$cot->precio_venta;
                    $cc->pu_neto=round($cc->pu/(1+Session::get('PARAM_IVA')),2);
                    $cc->subtotal_item=$cot->subtotal;
                    $cc->descuento_item=$cot->descuento;
                    $cc->total_item=$cot->total;
                    $cc->save();
                    $item++;
                }
                return "OK";
            }else{
                return "No existe la cotizacion ".$num_cotizacion;
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function envio_directo(){
        echo "Iniciando envio directo<br>";
        // datos del envío
        $xml = file_get_contents(base_path().'/xml/generados/directo.xml');
        $RutEnvia = '5483206-0';
        $RutEmisor = '5483206-0';

        // solicitar token
        $clave1="juana206"; // juana206 //panchorepuestos8311048
        $clave2="panchorepuestos8311048";
        $archivo_firma=base_path().'/cert/juanita_libreDTE.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
        }else{
            echo "no hay certificado firma<br>";
        }
        $token=Auto::getToken($Firma);
        echo "Token: ".$token."<br>";

        // enviar DTE
        $result = Sii::enviar($RutEnvia, $RutEmisor, $xml, $token);
        var_dump($result);
//$xml=$result['mensaje'];


        // Mostrar resultado del envío
        if ($xml->STATUS!='0') {
            echo"STATUS: ".$result->STATUS;
        }else{
            echo 'DTE envíado. Track ID '.$xml->TRACKID;
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
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

    public function probar_codint(){
        $max_id=repuesto::max('id');
        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=repuesto::where('repuestos.id',random_int(1,$max_id))
                    ->where('repuestos.activo',1)
                    ->value('codigo_interno');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codprov(){
        $max_id=repuesto::max('id');
        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=repuesto::where('repuestos.id',random_int(1,$max_id))
                    ->where('repuestos.activo',1)
                    ->value('cod_repuesto_proveedor');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codoem(){
        /*
SELECT repuestos.id,repuestos.codigo_interno,repuestos.descripcion,repuestos.version_vehiculo, repuestos.activo,oems.codigo_oem
FROM repuestos
inner join oems
on repuestos.id=oems.id_repuestos
where oems.codigo_oem='MS1173GP025'
        */

        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=oem::max('id');
        $id_repuestos=repuesto::select('id')
                                ->where('activo',1)
                                ->get()
                                ->toArray();
/*
        $valor=oem::select('oems.codigo_oem')->where('oems.id',random_int(1,$max_id))
                    ->wherein('oems.id_repuestos',$id_repuestos)
                    //->value('oems.codigo_oem')
                    ->toSql();
        dd($valor);
        */
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=oem::where('id',random_int(1,$max_id))
                            ->wherein('id_repuestos',$id_repuestos)
                            ->value('codigo_oem');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codfam(){
        $max_buscar=20;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=familia::max('id');

        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=familia::where('id',random_int(1,$max_id))
                            ->value('nombrefamilia');
            }while(is_null($valor));
            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codfab(){
        $max_buscar=20;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=fabricante::max('id');

        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=fabricante::where('id',random_int(1,$max_id))
                            ->value('codigo_fab');
            }while(is_null($valor));
            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function damedteporfechas($tipodte,$fechainicial,$fechafinal){
        if($tipodte=='33'){
            $dtes=factura::select('facturas.fecha_emision','facturas.created_at',
                                'facturas.num_factura',
                                'facturas.total',
                                'facturas.trackid',
                                'facturas.estado_sii',
                                'facturas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','facturas.id_cliente','clientes.id')
                        ->join('users','facturas.usuarios_id','users.id')
                        ->where('facturas.fecha_emision','>=',$fechainicial)
                        ->where('facturas.fecha_emision','<=',$fechafinal)
                        ->where('facturas.activo',1)
                        ->orderBy('facturas.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='39'){
            $dtes=boleta::select('boletas.fecha_emision','boletas.created_at',
                                'boletas.num_boleta',
                                'boletas.total',
                                'boletas.trackid',
                                'boletas.estado_sii',
                                'boletas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','boletas.id_cliente','clientes.id')
                        ->join('users','boletas.usuarios_id','users.id')
                        ->where('boletas.fecha_emision','>=',$fechainicial)
                        ->where('boletas.fecha_emision','<=',$fechafinal)
                        ->where('boletas.activo',1)
                        ->orderBy('boletas.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='61'){
            $dtes=nota_de_credito::select('notas_de_credito.fecha_emision','notas_de_credito.created_at',
                                'notas_de_credito.num_nota_credito',
                                'notas_de_credito.total',
                                'notas_de_credito.trackid',
                                'notas_de_credito.estado_sii',
                                'notas_de_credito.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','notas_de_credito.id_cliente','clientes.id')
                        ->join('users','notas_de_credito.usuarios_id','users.id')
                        ->where('notas_de_credito.fecha_emision','>=',$fechainicial)
                        ->where('notas_de_credito.fecha_emision','<=',$fechafinal)
                        ->where('notas_de_credito.activo',1)
                        ->orderBy('notas_de_credito.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='56'){

        }
        if($tipodte=='52'){
            $dtes=guia_de_despacho::select('guias_de_despacho.fecha_emision','guias_de_despacho.created_at',
                                'guias_de_despacho.num_guia_despacho',
                                'guias_de_despacho.total',
                                'guias_de_despacho.trackid',
                                'guias_de_despacho.estado_sii',
                                'guias_de_despacho.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','guias_de_despacho.id_cliente','clientes.id')
                        ->join('users','guias_de_despacho.usuarios_id','users.id')
                        ->where('guias_de_despacho.fecha_emision','>=',$fechainicial)
                        ->where('guias_de_despacho.fecha_emision','<=',$fechafinal)
                        ->where('guias_de_despacho.activo',1)
                        ->orderBy('guias_de_despacho.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
    }
}
