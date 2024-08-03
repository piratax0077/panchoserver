<?php

namespace App\Http\Controllers;

use Debugbar;
use Carbon\Carbon; // para tratamiento de fechas
use Illuminate\Http\Request;
use App\boleta;
use App\boleta_detalle;
use App\carrito_compra;
use App\carrito_guardado;
use App\cliente_modelo;
use App\correlativo;
use App\folio;
use App\cotizacion;
use App\cotizacion_detalle;
use App\descuento;
use App\fabricante;
use App\factura;
use App\factura_detalle;
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



    public function buscar_por_descripcion($dato)
    {
        //usando auth en el grupo de rutas. ver web.php
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("-","",$descripcion);
        $q = "nadita"; // Es el criterio de búsqueda discernido según el ingreso de texto del usuario en 1,2 ó 3 términos
        $numfil=0;
        $encontré=false;
        $repuestos=repuesto::where('id','fifi')->get(); //Para que devuelva un resultado vacio si no encuentra nada en ninguno de los algoritmos.
        //En este caso no es necesaria la familia para las búsquedas, entonces mas abajo (al terminar switch) se busca $fam en base a $fa
        //y al ponerle "nada de nada" no va a encontrar nada y no afectará el correr del algoritmo.
        $fa="nada de nada";

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
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
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
                        $numfil=fabricante::where('codigo_fab','LIKE',$buscado.'%')->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_fabricante";
                        }
                    }

                    //Por código de proveedor
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('cod_repuesto_proveedor', 'LIKE', $buscado. '%')
                        ->where('repuestos.activo',1)
                        ->count();

                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_proveedor";
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

                    // Al no encontrar nada, buscamos en la descripción
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('repuestos.descripcion', 'LIKE','%'.$buscado.'%')
                        ->where('repuestos.activo',1)
                        ->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="descripción";
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
                            $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
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
                        //Debugbar::info("num_fa: ".$num_f." num_mod: ".$num_mod);
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
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
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
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', $mod . '%')->count();
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
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
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
                        //Debugbar::info("fa: ".$num_f." mod: ".$num_mod);
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
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav . '%')->count();
                        $mod=trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', $mod . '%')->count();
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
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', $mod . '%')->count();
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
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', $mod . '%')->count();
                        $mar=trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', $mav. '%')->count();
                        if ($num_mav > 0) {
                            $q = "marcaveh";
                         } else { //No hay por marcaveh, buscamos por modeloveh o marca de repuesto
                            $mod = trim($d[2])." ".trim($d[3]); //modelo
                            $num_mod = modelovehiculo::where('modelonombre', 'LIKE', $mod. '%')->count();
                            if ($num_mod > 0) {
                                $q = "modelo";
                            } else { //No hay por modelo, buscamos por marca de repuesto
                                $mar = trim($d[2])." ".trim($d[3]); //marca repuesto
                                $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', $mar . '%')->count();
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
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE',$buscado.'%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $buscado . '%')
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
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $buscado. '%')
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
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $buscado. '%')
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
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $buscado. '%')
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
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $buscado. '%')
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
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $buscado . '%')
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $buscado. '%')
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
                        ->where('oems.codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
                        ->where('oems.codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
                        ->where('oems.codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
                    ->where('oems.codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
                    ->where('repuestos.medidas', 'LIKE', $buscado . '%')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', $buscado . '%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', $buscado . '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.medidas', 'LIKE', $buscado . '%')
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

            /* #region buscar en la descripción un término */
            if($q=='descripción')
            {
                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado.'%')
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
                        ->where('marcanombre', 'LIKE', $mav . '%')
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
                        ->where('modelonombre', 'LIKE', $mod. '%')
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
                    ->where('modelonombre', 'LIKE', $mod . '%')
                    ->get()
                    ->toArray();

                $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_modelo_vehiculo', $modelosveh)
                    ->get()
                    ->toArray();

                $marcasrep=marcarepuesto::select('id')
                                                ->where('marcarepuesto','LIKE',$mar.'%')
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
                    ->where('marcanombre', 'LIKE', $mav . '%')
                    ->get()
                    ->toArray();

                $modelosveh = modelovehiculo::select('id')
                    ->where('modelonombre', 'LIKE', $mod . '%')
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
                        ->where('marcanombre', 'LIKE', $mav . '%')
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
                        ->where('marcanombre', 'LIKE', $mav . '%')
                        ->get()
                        ->toArray();

                    $modelosveh = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', $mod . '%')
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
                        ->where('marcanombre', 'LIKE', $mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE',$mar.'%')
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
                        ->where('modelonombre', 'LIKE', $mod . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelosveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE',$mar.'%')
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
                                                    ->where('marcarepuesto','LIKE',$mar.'%')
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
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', $codigo_proveedor . '%')
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
        //Debugbar::info("sin: ".$buscado_sin_guion." con: ".$buscado_con_guion);
        if ($op == 0) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', $buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', $buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', $buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', $buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', $buscado_con_guion. '%')
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
        $fab = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $fab . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $fab . '%')
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
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $fab . '%')
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
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', $fab . '%')
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
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', $medidas . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', $medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', $medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', $medidas . '%')
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

    public function buscar_por_codigo_interno($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $codint = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $codint . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $codint . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $codint . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', $codint . '%')
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
        $criterio="por_codigo_interno";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','tienen_foto'))->render();
        return $v;
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

            if ($op == 0) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                INNER JOIN familias ON repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
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
            $v = view('fragm.ventas_familias', compact('familias', 'dato', 'total_repuestos'))->render();
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
            $num = correlativo::where('id_local', $id_local)
            ->where('documento', $tip_doc)
            ->value('correlativo');
        }else{
            $fila=correlativo::where('id_local', $id_local)
            ->where('documento', $tip_doc)
            ->first();
            if(!is_null($fila))
            {
                $corr=$fila->correlativo;
                $max_folio=$fila->hasta;
                if($max_folio>=($corr+1)) $num=$corr;
            }
        }
        return $num;
    }

    public function generar_xml(Request $r)
    {

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
            //FALTA: PARA GUIA DE DESPACHO
        }


        //Obtener cliente

        if($r->idcliente==0){
            $idcliente=$this->dame_cliente_6();
        }else{
            $idcliente=$r->idcliente;
        }

        $cliente=cliente_modelo::find($idcliente);

        if($cliente->rut==0){
            $rutCliente_con_guion='00000000-0'; // no funciona tampoco con 6666 lo rechaza SII
        }else{
            $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
        }
        //$rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                            'RznSocRecep'=>$cliente->razon_social,
                            'GiroRecep'=>$cliente->giro,
                            'DirRecep'=>$cliente->direccion,
                            'CmnaRecep'=>$cliente->direccion_comuna.". ".$cliente->direccion_ciudad,
                        ];

        //Obtener detalle del carrito
        $Detalle=[];
        $carrito=new carrito_compra();
        $c=$carrito->dame_todo_carrito();
        foreach($c as $i)
        {
            //$precio_neto_item=$i->pu_neto; //round($i->pu/(1+Session::get('PARAM_IVA')),0);
            $item=array('NmbItem'=>$i->descripcion,
                                'QtyItem'=>$i->cantidad,
                                'PrcItem'=>intval($i->pu_neto));
            array_push($Detalle,$item);
        }

        $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array

        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre',$r->docu);
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente',$idcliente);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
        }

        return json_encode($estado);
    }

    public function enviar_sii($id_cliente)
    {

        if(Session::get('xml')==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT'));
        $RutEmisor = $RutEnvia;
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
                Debugbar::error("error estado");
                $TrackID="---";
                $estado_sii=$rs['estado'];
            }
            switch ($tipo_dte){
                case '39':
                    $b = new boleta;
                    $b->num_boleta = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                    $b->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                    $b->id_cliente = $id_cliente;
                    $b->estado = $estado;
                    $b->estado_sii=$estado_sii;
                    $b->resultado_envio=$resultado_envio;
                    $b->trackid=$TrackID;
                    $b->url_xml=$d;
                    $b->total =round(intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal)*(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP); //incluye el iva
                    $b->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
                    $b->exento = 0;
                    $b->iva = round($b->neto*Session::get('PARAM_IVA'),0,PHP_ROUND_HALF_UP);
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
                        $bd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem)*(1+Session::get('PARAM_IVA')); //$i->pu;
                        $bd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem)*(1+Session::get('PARAM_IVA'));
                        $bd->subtotal = $sb;//$i->subtotal_item;
                        $bd->descuento = $i->descuento_item;
                        $bd->total = $sb-$i->descuento_item;
                        $bd->activo = 1;
                        $bd->usuarios_id = Session::get('usuario_id');
                        $bd->save();
                    }

                    $tipo_docu="boleta";
                    $num_docu=$b->num_boleta;
                break;
                case '33':
                    Debugbar::info("Factura Cabecera");
                    $f = new factura;
                    $f->num_factura = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                    $f->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
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
                        $fd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem)*(1+Session::get('PARAM_IVA')); //$i->pu;
                        $fd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem)*(1+Session::get('PARAM_IVA'));
                        $fd->subtotal = $sb;//$i->subtotal_item;
                        $fd->descuento = $i->descuento_item;
                        $fd->total = $sb-$i->descuento_item;
                        $fd->activo = 1;
                        $fd->usuarios_id = Session::get('usuario_id');
                        $fd->save();
                    }
                    $tipo_docu="factura";
                    $num_docu=$f->num_factura;
                break;
                case '61':

                break;
                case '56':

                break;

            }//kaka
            $this->actualizar_correlativo($tipo_docu, $num_docu);
        } catch (\Exception $e) {
            Debugbar::error($e->getMessage());
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

    public function guardar_venta(Request $r){
        //usando auth en el grupo de rutas. ver web.php
        $carrito=new carrito_compra();
        ////Debugbar::addMessage('ventas_controlador: Instancia carrito_compra','depurador');
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
                    //Debugbar::info("estado: ".$rs['estado']);
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
        Debugbar::info("Actualizac correlativo para ".$docu." núm: ".$num);
        $co = correlativo::where('documento', $docu)
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $s=$co->save();
        Debugbar::info($s);
    }

    private function dameultimoitem($id_usu)
    {

        $ultimo = carrito_compra::where('usuarios_id', $id_usu)->latest()->value('item');
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
            $c->pu_neto=$repuesto->pu_neto; // sin iva
            $c->pu = $repuesto->precio_venta; //Ya incluye el IVA
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
        Debugbar::info("dame_carritos_guardados");
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
        $c = cliente_modelo::select('tipo_descuento', 'porcentaje')
            ->where('id', $id_cliente)
            ->first();
        $cc = carrito_compra::where('usuarios_id', Session::get('usuario_id'))->get();

        if ($c->tipo_descuento == 0) // Sin Descuento
        {
            foreach ($cc as $item) {
                $item->descuento_item = 0;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }

        }

        if ($c->tipo_descuento == 1) //Descuento simple
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

        if ($c->tipo_descuento == 3) //Descuento por familia
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

    private function dame_cliente_6()
    {
        $rpta=-1; //No esta definido el cliente 0000000
        $c0=cliente_modelo::where('rut','LIKE','66666%')->first();
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

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 15 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_15=$hoy->subDays(15);
        $borrados1=cotizacion::where('created_at','<=',$fecha_15)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<=',$fecha_15)
                                            ->delete();

        $cot=cotizacion::select(\DB::raw('id,num_cotizacion,DATE_FORMAT(fecha_emision,\'%d-%m-%Y\') as fecha'))
                                ->where('id_cliente',$id_cliente)
                                ->where('fecha_expira','>=',$fecha_hoy)
                                ->where('fecha_emision','<=',$fecha_hoy)
                                ->orderBy('fecha_emision','DESC')
                                ->get()->toJson();
        return $cot;
    }

    public function cargar_cotizacion($num_cotizacion)
    {
        $el_id_usuario=Session::get('usuario_id');
        //borrar el carrito activo
        $this->borrar_carrito('actual');
        //copiar el carrito guardado hacia el carrito activo
        $id_cotizacion=cotizacion::where('id',$num_cotizacion)
                                            ->value('id');
        $cotizacion=cotizacion_detalle::where(@id_cotizacion,$id_cotizacion)->get();
        foreach($cotizacion as $cot)
        {
            $cc=new carrito_compra;
            $cc->usuarios_id=$el_id_usuario;
            $cc->item=$cot->id;
            $cc->id_repuestos=$cot->id_repuestos;
            $cc->id_local=$cot->id_local;
            $cc->id_unidad_venta=$cot->id_unidad_venta;
            $cc->cantidad=$cot->cantidad;
            $cc->pu=$cot->precio_venta;
            $cc->subtotal_item=$cot->subtotal;
            $cc->descuento_item=$cot->descuento;
            $cc->total_item=$cot->total;
            $cc->save();
        }
        return "OK";
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
}
