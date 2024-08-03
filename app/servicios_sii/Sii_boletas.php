<?php
namespace App\servicios_sii;
use Illuminate\Support\Facades\Http;
use Debugbar;
use SoapClient;

class Sii_boletas
{

    private static $config = [
        'wsdl' => [
            '*' => 'https://{servidor}.sii.cl/DTEWS/{servicio}.jws?WSDL',
            'QueryEstDteAv' => 'https://{servidor}.sii.cl/DTEWS/services/{servicio}?WSDL',
            'wsDTECorreo' => 'https://{servidor}.sii.cl/DTEWS/services/{servicio}?WSDL',
        ],
        'servidor' => ['palena', 'maullin'], ///< servidores 0: producción, 1: certificación
        'certs' => [300, 100], ///< certificados 0: producción, 1: certificación
    ];
    const SERVIDOR_CERTIFICACION_APICERT="https://apicert.sii.cl/recursos/v1";
    const SERVIDOR_CERTIFICACION_PANGAL="https://pangal.sii.cl/recursos/v1";
    const SERVIDOR_PRODUCCION_API="https://api.sii.cl/recursos/v1";
    const SERVIDOR_PRODUCCION_RAHUE="https://rahue.sii.cl/recursos/v1";


    const PRODUCCION = 0; ///< Constante para indicar ambiente de producción
    const CERTIFICACION = 1; ///< Constante para indicar ambiente de desarrollo

    const IVA = 19; ///< Tasa de IVA

    private static $retry = 10; ///< Veces que se reintentará conectar a SII al usar el servicio web
    //En producción ponerle true a verificar_ssl
    private static $verificar_ssl = false; ///< Indica si se deberá verificar o no el certificado SSL del SII
    private static $ambiente = self::PRODUCCION; ///< Ambiente que se utilizará

    private static $direcciones_regionales = [
        'ARICA' => 'ARICA',
        'CAMARONES' => 'ARICA',
        'PUTRE' => 'ARICA',
        'GENERAL LAGOS' => 'ARICA',
        'IQUIQUE' => 'IQUIQUE',
        'PICA' => 'IQUIQUE',
        'POZO ALMONTE' => 'IQUIQUE',
        'HUARA' => 'IQUIQUE',
        'CAMIÑA' => 'IQUIQUE',
        'COLCHANE' => 'IQUIQUE',
        'ALTO HOSPICIO' => 'IQUIQUE',
        'ANTOFAGASTA' => 'ANTOFAGASTA',
        'MEJILLONES' => 'ANTOFAGASTA',
        'SIERRA GORDA' => 'ANTOFAGASTA',
        'CALAMA' => 'CALAMA',
        'SAN PEDRO DE ATACAMA' => 'CALAMA',
        'OLLAGUE' => 'CALAMA',
        'TOCOPILLA' => 'TOCOPILLA',
        'MARÍA ELENA' => 'TOCOPILLA',
        'TALTAL' => 'TALTAL',
        'COPIAPÓ' => 'COPIAPÓ',
        'CALDERA' => 'COPIAPÓ',
        'TIERRA AMARILLA' => 'COPIAPÓ',
        'CHAÑARAL' => 'CHAÑARAL',
        'DIEGO DE ALMAGRO' => 'CHAÑARAL',
        'VALLENAR' => 'VALLENAR',
        'FREIRINA' => 'VALLENAR',
        'HUASCO' => 'VALLENAR',
        'ALTO DEL CARMEN' => 'VALLENAR',
        'LA SERENA' => 'LA SERENA',
        'LA HIGUERA' => 'LA SERENA',
        'PAIHUANO' => 'LA SERENA',
        'ANDACOLLO' => 'LA SERENA',
        'VICUÑA' => 'LA SERENA',
        'OVALLE' => 'OVALLE',
        'MONTE PATRIA' => 'OVALLE',
        'PUNITAQUI' => 'OVALLE',
        'COMBARBALÁ' => 'OVALLE',
        'RÍO HURTADO' => 'OVALLE',
        'ILLAPEL' => 'ILLAPEL',
        'SALAMANCA' => 'ILLAPEL',
        'LOS VILOS' => 'ILLAPEL',
        'CANELA' => 'ILLAPEL',
        'COMQUIMBO' => 'COQUIMBO',
        'VALPARAÍSO' => 'VALPARAÍSO',
        'CASABLANCA' => 'VALPARAÍSO',
        'JUAN FERNÁNDEZ' => 'VALPARAÍSO',
        'ISLA DE PASCUA' => 'VALPARAÍSO',
        'CONCÓN' => 'VIÑA DEL MAR',
        'QUINTERO' => 'VIÑA DEL MAR',
        'PUCHUNCAVÍ' => 'VIÑA DEL MAR',
        'VIÑA DEL MAR' => 'VIÑA DEL MAR',
        'LA LIGUA' => 'LA LIGUA',
        'PETORCA' => 'LA LIGUA',
        'CABILDO' => 'LA LIGUA',
        'ZAPALLAR' => 'LA LIGUA',
        'PAPUDO' => 'LA LIGUA',
        'SAN ANTONIO' => 'SAN ANTONIO',
        'SANTO DOMINGO' => 'SAN ANTONIO',
        'CARTAGENA' => 'SAN ANTONIO',
        'EL TABO' => 'SAN ANTONIO',
        'EL QUISCO' => 'SAN ANTONIO',
        'ALGARROBO' => 'SAN ANTONIO',
        'QUILLOTA' => 'QUILLOTA',
        'NOGALES' => 'QUILLOTA',
        'HIJUELAS' => 'QUILLOTA',
        'LA CALERA' => 'QUILLOTA',
        'LA CRUZ' => 'QUILLOTA',
        'LIMACHE' => 'QUILLOTA',
        'OLMUÉ' => 'QUILLOTA',
        'SAN FELIPE' => 'SAN FELIPE',
        'PANQUEHUE' => 'SAN FELIPE',
        'CATEMU' => 'SAN FELIPE',
        'PUTAENDO' => 'SAN FELIPE',
        'SANTA MARÍA' => 'SAN FELIPE',
        'LLAY LLAY' => 'SAN FELIPE',
        'LOS ANDES' => 'LOS ANDES',
        'CALLE LARGA' => 'LOS ANDES',
        'SAN ESTEBAN' => 'LOS ANDES',
        'RINCONADA' => 'LOS ANDES',
        'VILLA ALEMANA' => 'VILLA ALEMANA',
        'QUILPUÉ' => 'VILLA ALEMANA',
        'RANCAGUA' => 'RANCAGUA',
        'MACHALÍ' => 'RANCAGUA',
        'GRANEROS' => 'RANCAGUA',
        'SAN FRANCISCO DE MOSTAZAL' => 'RANCAGUA',
        'DOÑIHUE' => 'RANCAGUA',
        'CODEGUA' => 'RANCAGUA',
        'RENGO' => 'RANCAGUA',
        'COLTAUCO' => 'RANCAGUA',
        'REQUINOA' => 'RANCAGUA',
        'OLIVAR' => 'RANCAGUA',
        'MALLOA' => 'RANCAGUA',
        'COINCO' => 'RANCAGUA',
        'QUINTA DE TILCOCO' => 'RANCAGUA',
        'SAN FERNANDO' => 'SAN FERNANDO',
        'CHIMBARONGO' => 'SAN FERNANDO',
        'NANCAGUA' => 'SAN FERNANDO',
        'PLACILLA' => 'SAN FERNANDO',
        'SANTA CRUZ' => 'SANTA CRUZ',
        'LOLOL' => 'SANTA CRUZ',
        'PALMILLA' => 'SANTA CRUZ',
        'PERALILLO' => 'SANTA CRUZ',
        'CHÉPICA' => 'SANTA CRUZ',
        'PUMANQUE' => 'SANTA CRUZ',
        'SAN VICENTE' => 'SAN VICENTE TAGUA TAGUA',
        'LAS CABRAS' => 'SAN VICENTE TAGUA TAGUA',
        'PEUMO' => 'SAN VICENTE TAGUA TAGUA',
        'PICHIDEGUA' => 'SAN VICENTE TAGUA TAGUA',
        'PICHILEMU' => 'PICHILEMU',
        'PAREDONES' => 'PICHILEMU',
        'MARCHIGUE' => 'PICHILEMU',
        'LITUECHE' => 'PICHILEMU',
        'LA ESTRELLA' => 'PICHILEMU',
        'TALCA' => 'TALCA',
        'SAN CLEMENTE' => 'TALCA',
        'PELARCO' => 'TALCA',
        'RÍO CLARO' => 'TALCA',
        'PENCAHUE' => 'TALCA',
        'MAULE' => 'TALCA',
        'CUREPTO' => 'TALCA',
        'SAN JAVIER' => 'TALCA',
        'LINARES' => 'LINARES',
        'YERBAS BUENAS' => 'LINARES',
        'COLBÚN' => 'LINARES',
        'LONGAVÍ' => 'LINARES',
        'VILLA ALEGRE' => 'LINARES',
        'CONSTITUCIÓN' => 'CONSTITUCIÓN',
        'EMPEDRADO' => 'CONSTITUCIÓN',
        'CAUQUENES' => 'CAUQUENES',
        'PELLUHUE' => 'CAUQUENES',
        'CHANCO' => 'CAUQUENES',
        'PARRAL' => 'PARRAL',
        'RETIRO' => 'PARRAL',
        'CURICÓ' => 'CURICÓ',
        'TENO' => 'CURICÓ',
        'ROMERAL' => 'CURICÓ',
        'MOLINA' => 'CURICÓ',
        'HUALAÑE' => 'CURICÓ',
        'SAGRADA FAMILIA' => 'CURICÓ',
        'LICANTÉN' => 'CURICÓ',
        'VICHUQUÉN' => 'CURICÓ',
        'RAUCO' => 'CURICÓ',
        'CONCEPCIÓN' => 'CONCEPCIÓN',
        'CHIGUAYANTE' => 'CONCEPCIÓN',
        'SAN PEDRO DE LA PAZ' => 'CONCEPCIÓN',
        'PENCO' => 'CONCEPCIÓN',
        'HUALQUI' => 'CONCEPCIÓN',
        'FLORIDA' => 'CONCEPCIÓN',
        'TOMÉ' => 'CONCEPCIÓN',
        'CORONEL' => 'CONCEPCIÓN',
        'LOTA' => 'CONCEPCIÓN',
        'SANTA JUANA' => 'CONCEPCIÓN',
        'ARAUCO' => 'CONCEPCIÓN',
        'CHILLÁN' => 'CHILLÁN',
        'PINTO' => 'CHILLÁN',
        'EL CARMEN' => 'CHILLÁN',
        'SAN IGNACIO' => 'CHILLÁN',
        'PEMUCO' => 'CHILLÁN',
        'YUNGAY' => 'CHILLÁN',
        'BULNES' => 'CHILLÁN',
        'QUILLÓN' => 'CHILLÁN',
        'RANQUIL' => 'CHILLÁN',
        'PORTEZUELO' => 'CHILLÁN',
        'COELEMU' => 'CHILLÁN',
        'TREHUACO' => 'CHILLÁN',
        'QUIRIHUE' => 'CHILLÁN',
        'COBQUECURA' => 'CHILLÁN',
        'NINHUE' => 'CHILLÁN',
        'CHILLÁN VIEJO' => 'CHILLÁN',
        'LOS ÁNGELES' => 'LOS ÁNGELES',
        'SANTA BARBARA' => 'LOS ÁNGELES',
        'LAJA' => 'LOS ÁNGELES',
        'QUILLECO' => 'LOS ÁNGELES',
        'NACIMIENTO' => 'LOS ÁNGELES',
        'NEGRETE' => 'LOS ÁNGELES',
        'MULCHÉN' => 'LOS ÁNGELES',
        'QUILACO' => 'LOS ÁNGELES',
        'YUMBEL' => 'LOS ÁNGELES',
        'CABRERO' => 'LOS ÁNGELES',
        'SAN ROSENDO' => 'LOS ÁNGELES',
        'TUCAPEL' => 'LOS ÁNGELES',
        'ANTUCO' => 'LOS ÁNGELES',
        'ALTO BÍO-BÍO' => 'LOS ÁNGELES',
        'SAN CARLOS' => 'SAN CARLOS',
        'SAN GREGORIO DE ÑINQUEN' => 'SAN CARLOS',
        'SAN NICOLÁS' => 'SAN CARLOS',
        'SAN FABIÁN DE ALICO' => 'SAN CARLOS',
        'TALCAHUANO' => 'TALCAHUANO',
        'HUALPÉN' => 'TALCAHUANO',
        'LEBU' => 'LEBU',
        'CURANILAHUE' => 'LEBU',
        'LOS ALAMOS' => 'LEBU',
        'CAÑETE' => 'LEBU',
        'CONTULMO' => 'LEBU',
        'TIRÚA' => 'LEBU',
        'TEMUCO' => 'TEMUCO',
        'VILCÚN' => 'TEMUCO',
        'FREIRE' => 'TEMUCO',
        'CUNCO' => 'TEMUCO',
        'LAUTARO' => 'TEMUCO',
        'PERQUENCO' => 'TEMUCO',
        'GALVARINO' => 'TEMUCO',
        'NUEVA IMPERIAL' => 'TEMUCO',
        'CARAHUE' => 'TEMUCO',
        'PUERTO SAAVEDRA' => 'TEMUCO',
        'PITRUFQUÉN' => 'TEMUCO',
        'GORBEA' => 'TEMUCO',
        'TOLTÉN' => 'TEMUCO',
        'LONCOCHE' => 'TEMUCO',
        'MELIPEUCO' => 'TEMUCO',
        'TEODORO SCHMIDT' => 'TEMUCO',
        'PADRE LAS CASAS' => 'TEMUCO',
        'CHOLCHOL' => 'TEMUCO',
        'ANGOL' => 'ANGOL',
        'PURÉN' => 'ANGOL',
        'LOS SAUCES' => 'ANGOL',
        'REINACO' => 'ANGOL',
        'COLLIPULLI' => 'ANGOL',
        'ERCILLA' => 'ANGOL',
        'VICTORIA' => 'VICTORIA',
        'TRAIGUÉN' => 'VICTORIA',
        'LUMACO' => 'VICTORIA',
        'CURACAUTÍN' => 'VICTORIA',
        'LONQUIMAY' => 'VICTORIA',
        'VILLARRICA' => 'VILLARRICA',
        'PUCÓN' => 'VILLARRICA',
        'CURARREHUE' => 'VILLARRICA',
        'VALDIVIA' => 'VALDIVIA',
        'MARIQUINA' => 'VALDIVIA',
        'LANCO' => 'LANCO',
        'MÁFIL' => 'VALDIVIA',
        'CORRAL' => 'VALDIVIA',
        'LOS LAGOS' => 'VALDIVIA',
        'PAILLACO' => 'VALDIVIA',
        'PANGUIPULLI' => 'PANGUIPULLI',
        'LA UNIÓN' => 'LA UNIÓN',
        'FUTRONO' => 'VALDIVIA',
        'RÍO BUENO' => 'LA UNIÓN',
        'LAGO RANCO' => 'LA UNIÓN',
        'PUERTO MONTT' => 'PUERTO MONTT',
        'CALBUCO' => 'PUERTO MONTT',
        'MAULLÍN' => 'PUERTO MONTT',
        'LOS MUERMOS' => 'PUERTO MONTT',
        'HUALAIHUÉ' => 'PUERTO MONTT',
        'PUERTO VARAS' => 'PUERTO VARAS',
        'COCHAMÓ' => 'PUERTO VARAS',
        'FRESIA' => 'PUERTO VARAS',
        'LLANQUIHUE' => 'PUERTO VARAS',
        'FRUTILLAR' => 'PUERTO VARAS',
        'ANCUD' => 'ANCUD',
        'QUEMCHI' => 'ANCUD',
        'OSORNO' => 'OSORNO',
        'PUYEHUE' => 'OSORNO',
        'PURRANQUE' => 'OSORNO',
        'RÍO NEGRO' => 'OSORNO',
        'SAN PABLO' => 'OSORNO',
        'SAN JUAN DE LA COSTA' => 'OSORNO',
        'PUERTO OCTAY' => 'OSORNO',
        'CASTRO' => 'CASTRO',
        'CURACO DE VÉLEZ' => 'CASTRO',
        'CHOCHI' => 'CASTRO',
        'DALCAHUE' => 'CASTRO',
        'PUQUELDÓN' => 'CASTRO',
        'QUEILÉN' => 'CASTRO',
        'QUELLÓN' => 'CASTRO',
        'CHAITÉN' => 'CHAITÉN',
        'PALENA' => 'CHAITÉN',
        'FUTALEUFÚ' => 'CHAITÉN',
        'COYHAIQUE' => 'COYHAIQUE',
        'RÍO IBAÑEZ' => 'COYHAIQUE',
        'O`HIGGINS' => 'COCHRANE',
        'TORTEL' => 'COCHRANE',
        'AYSÉN' => 'AYSÉN',
        'CISNES' => 'AYSÉN',
        'LAGO VERDE' => 'AYSÉN',
        'GUAITECAS' => 'AYSÉN',
        'CHILE CHICO' => 'CHILE CHICO',
        'COCHRANE' => 'COCHRANE',
        'GUADAL' => 'COCHRANE',
        'PUERTO BELTRAND' => 'COCHRANE',
        'PUNTA ARENAS' => 'PUNTA ARENAS',
        'RÍO VERDE' => 'PUNTA ARENAS',
        'SAN GREGORIO' => 'PUNTA ARENAS',
        'LAGUNA BLANCA' => 'PUNTA ARENAS',
        'CABO DE HORNOS' => 'PUNTA ARENAS',
        'PUERTO NATALES' => 'PUERTO NATALES',
        'TORRES DEL PAINE' => 'PUERTO NATALES',
        'PORVENIR' => 'PORVENIR',
        'PRIMAVERA' => 'PORVENIR',
        'TIMAUKEL' => 'PORVENIR',
        'INDEPENDENCIA' => 'SANTIAGO NORTE',
        'RECOLETA' => 'SANTIAGO NORTE',
        'HUECHURABA' => 'SANTIAGO NORTE',
        'CONCHALÍ' => 'SANTIAGO NORTE',
        'QUILICURA' => 'SANTIAGO NORTE',
        'COLINA' => 'SANTIAGO NORTE',
        'LAMPA' => 'SANTIAGO NORTE',
        'TILTIL' => 'SANTIAGO NORTE',
        'SANTIAGO' => 'SANTIAGO CENTRO',
        'CERRO NAVIA' => 'SANTIAGO PONIENTE',
        'CURACAVÍ' => 'SANTIAGO PONIENTE',
        'ESTACIÓN CENTRAL' => 'SANTIAGO PONIENTE',
        'LO PRADO' => 'SANTIAGO PONIENTE',
        'PUDAHUEL' => 'SANTIAGO PONIENTE',
        'QUINTA NORMAL' => 'SANTIAGO PONIENTE',
        'RENCA' => 'SANTIAGO PONIENTE',
        'MELIPILLA' => 'MELIPILLA',
        'SAN PEDRO' => 'MELIPILLA',
        'ALHUÉ' => 'MELIPILLA',
        'MARÍA PINTO' => 'MELIPILLA',
        'MAIPÚ' => 'MAIPÚ',
        'CERRILLOS' => 'MAIPÚ',
        'PADRE HURTADO' => 'MAIPÚ',
        'PEÑAFLOR' => 'MAIPÚ',
        'TALAGANTE' => 'MAIPÚ',
        'EL MONTE' => 'MAIPÚ',
        'ISLA DE MAIPO' => 'MAIPÚ',
        'LAS CONDES' => 'SANTIAGO ORIENTE',
        'VITACURA' => 'SANTIAGO ORIENTE',
        'LO BARNECHEA' => 'SANTIAGO ORIENTE',
        'ÑUÑOA' => 'ÑUÑOA',
        'LA REINA' => 'ÑUÑOA',
        'MACUL' => 'ÑUÑOA',
        'PEÑALOLÉN' => 'ÑUÑOA',
        'PROVIDENCIA' => 'PROVIDENCIA',
        'SAN MIGUEL' => 'SANTIAGO SUR',
        'LA CISTERNA' => 'SANTIAGO SUR',
        'SAN JOAQUÍN' => 'SANTIAGO SUR',
        'PEDRO AGUIRRE CERDA' => 'SANTIAGO SUR',
        'LO ESPEJO' => 'SANTIAGO SUR',
        'LA GRANJA' => 'SANTIAGO SUR',
        'LA PINTANA' => 'SANTIAGO SUR',
        'SAN RAMÓN' => 'SANTIAGO SUR',
        'LA FLORIDA' => 'LA FLORIDA',
        'PUENTE ALTO' => 'LA FLORIDA',
        'PIRQUE' => 'LA FLORIDA',
        'SAN JOSÉ DE MAIPO' => 'LA FLORIDA',
        'SAN BERNARDO' => 'SAN BERNARDO',
        'CALERA DE TANGO' => 'SAN BERNARDO',
        'EL BOSQUE' => 'SAN BERNARDO',
        'BUIN' => 'BUIN',
        'PAINE' => 'BUIN',
    ]; /// Direcciones regionales del SII según la comuna

    /**
     * Método que permite asignar el nombre del servidor del SII que se
     * usará para las consultas al SII
     * @param servidor Servidor que se usará: maullin (certificación) o palena (producción)
     * @param certificacion Permite definir si se está cambiando el servidor de certificación o el de producción
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-14
     */
    public static function setServidor($servidor = 'maullin', $certificacion = Sii::CERTIFICACION)
    {
        self::$config['servidor'][$certificacion] = $servidor;
    }

    /**
     * Método que entrega el nombre del servidor a usar según el ambiente
     * @param ambiente Ambiente que se desea obtener el servidor, si es null se autodetectará
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-01
     */
    public static function getServidor($ambiente = null)
    {
        return self::$config['servidor'][self::getAmbiente($ambiente)];
    }

    /**
     * Método que entrega la URL de un recurso en el SII según el ambiente que se esté usando
     * @param recurso Recurso del sitio del SII que se desea obtener la URL
     * @param ambiente Ambiente que se desea obtener el servidor, si es null se autodetectará
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public static function getURL($recurso, $ambiente = null)
    {
        $ambiente = self::getAmbiente($ambiente);
        // si es anulación masiva de folios
        if ($recurso=='/anulacionMsvDteInternet') {
            $servidor = $ambiente ? 'www4c' : 'www4';
        }
        // servidor estandar (maullin o palena)
        else {
            $servidor = self::getServidor($ambiente);
        }
        // entregar URL
        return 'https://'.$servidor.'.sii.cl'.$recurso;
    }

    /**
     * Método para obtener el WSDL
     *
     * \code{.php}
     *   $wsdl = \sasco\LibreDTE\Sii::wsdl('CrSeed'); // WSDL para pedir semilla
     * \endcode
     *
     * Para forzar el uso del WSDL de certificación hay dos maneras, una es
     * pasando un segundo parámetro al método get con valor Sii::CERTIFICACION:
     *
     * \code{.php}
     *   $wsdl = \sasco\LibreDTE\Sii::wsdl('CrSeed', \sasco\LibreDTE\Sii::CERTIFICACION);
     * \endcode
     *
     * La otra manera, para evitar este segundo parámetro, es asignar el valor a
     * través de la configuración:
     *
     * \code{.php}
     *   \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);
     * \endcode
     *
     * @param servicio Servicio por el cual se está solicitando su WSDL
     * @param ambiente Ambiente a usar: Sii::PRODUCCION o Sii::CERTIFICACION o null (para detección automática)
     * @return URL del WSDL del servicio según ambiente solicitado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public static function wsdl($servicio, $ambiente = null)
    {
        // determinar ambiente que se debe usar
        $ambiente = self::getAmbiente($ambiente);

        // entregar WSDL local (modificados para ambiente de certificación)
        if ($ambiente==self::CERTIFICACION) {
            $wsdl = dirname(dirname(__FILE__)).'/wsdl/'.self::$config['servidor'][$ambiente].'/'.$servicio.'.jws';
            if (is_readable($wsdl))
                return $wsdl;
        }

        // entregar WSDL oficial desde SII
        $location = isset(self::$config['wsdl'][$servicio]) ? self::$config['wsdl'][$servicio] : self::$config['wsdl']['*'];
        $wsdl = str_replace(
            ['{servidor}', '{servicio}'],
            [self::$config['servidor'][$ambiente], $servicio],
            $location
        );
        // entregar wsdl

        return $wsdl;
    }

    public static function request($metodo,$url,$params=[],$envio=false,$retry=null){

        $ambiente = self::getAmbiente();
        if($ambiente==self::PRODUCCION){
            if($envio===true){
                $servidor=self::SERVIDOR_PRODUCCION_RAHUE;
            }else{
                $servidor=self::SERVIDOR_PRODUCCION_API;
            }
        }else{
            if($envio===true){
                $servidor=self::SERVIDOR_CERTIFICACION_PANGAL;
            }else{
                $servidor=self::SERVIDOR_CERTIFICACION_APICERT;
            }
        }
/*
        if ($params and !is_array($params)) {
            $params = [$params];
        }
*/
        if($metodo=='GET'){
            $response=Http::withHeaders(
                ['accept' => 'application/xml']
            )->get($servidor.$url,$params);
        }
        if($metodo=='POST'){

            //$cliente= new \GuzzleHttp\Client();
            //$request=$cliente->createRequest('POST',$servidor.$url);
            //$request->setHeader('Content-Type','application/xml;charset=UTF8');
            //$request->setBody($params,'application/xml');

            //$options=['headers'=>['Content-Type'=>'application/xml;charset=UTF8']];
            //$response=$cliente->send($request);


            /*
            https://docs.guzzlephp.org/en/5.3/http-messages.html?highlight=setbody#body

$request = $client->createRequest('PUT', 'http://httpbin.org/put');
$request->setBody(Stream::factory('foo'));


*/


            $response=Http::withBody($params,'application/xml')
                            ->retry(10,200)
                            ->post($servidor.$url);

        }

        //if($response->getStatusCode()==200){
        if($response->successful()){
            $body=$response->getBody();
            //echo $body;
            //Debugbar::info($body);
            return new \SimpleXMLElement($body, LIBXML_COMPACT);
        }
        return false;

    }

    public static function request_json($metodo,$url,$params=[],$envio=true,$token,$retry=null){

        $ambiente = self::getAmbiente();
        if($ambiente==self::PRODUCCION){
            if($envio===true){
                $servidor=self::SERVIDOR_PRODUCCION_RAHUE;
            }else{
                $servidor=self::SERVIDOR_PRODUCCION_API;
            }
        }else{
            if($envio===true){
                $servidor=self::SERVIDOR_CERTIFICACION_PANGAL;
            }else{
                $servidor=self::SERVIDOR_CERTIFICACION_APICERT;
            }
        }
        $headers=[
            'accept: application/json',
            'Cookie: TOKEN='.$token
        ];
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, $servidor.$url."/".$params);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!$retry) {
            $retry = 10;
        }

        for ($i=0; $i<$retry; $i++) {
            $response = curl_exec($curl);
            if ($response and $response!='Error 500') {
                break;
            }
        }
        curl_close($curl);
        $r=json_decode($response, true);
        return $r;
    }

    /**
     * Método que permite indicar si se debe o no verificar el certificado SSL
     * del SII
     * @param verificar =true si se quiere verificar certificado, =false en caso que no (por defecto se verifica)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-16
     */
    public static function setVerificarSSL($verificar = true)
    {
        self::$verificar_ssl = $verificar;
    }

    /**
     * Método que indica si se está o no verificando el SSL en las conexiones al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-05-11
     */
    public static function getVerificarSSL()
    {
        return self::$verificar_ssl;
    }

    /**
     * Método que realiza el envío de un DTE al SII
     * Referencia: http://www.sii.cl/factura_electronica/factura_mercado/envio.pdf
     * @param usuario RUN del usuario que envía el DTE
     * @param empresa RUT de la empresa emisora del DTE
     * @param dte Documento XML con el DTE que se desea enviar a SII
     * @param token Token de autenticación automática ante el SII
     * @param gzip Permite enviar el archivo XML comprimido al servidor
     * @param retry Intentos que se realizarán como máximo para obtener respuesta
     * @return Respuesta XML desde SII o bien null si no se pudo obtener respuesta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-10-23
     */
    public static function enviar($usuario, $empresa, $dte, $token, $envio=true,$gzip = false, $retry = null)
    {
        $error=false;
        $ambiente = self::getAmbiente();
        if($ambiente==self::PRODUCCION){
            if($envio===true){
                $servidor=self::SERVIDOR_PRODUCCION_RAHUE;
            }else{
                $servidor=self::SERVIDOR_PRODUCCION_API;
            }
        }else{
            if($envio===true){
                $servidor=self::SERVIDOR_CERTIFICACION_PANGAL;
            }else{
                $servidor=self::SERVIDOR_CERTIFICACION_APICERT;
            }
        }

        // definir datos que se usarán en el envío
        list($rutSender, $dvSender) = explode('-', str_replace('.', '', $usuario));
        list($rutCompany, $dvCompany) = explode('-', str_replace('.', '', $empresa));
        if (strpos($dte, '<?xml')===false) {
            $dte = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n".$dte;
        }
        do {
            $file = sys_get_temp_dir().'/dte_'.md5(microtime().$token.$dte).'.'.($gzip?'gz':'xml');
        } while (file_exists($file));
        if ($gzip) {
            $dte = gzencode($dte);
            if ($dte===false) {
                //Log::write(Estado::ENVIO_ERROR_GZIP, Estado::get(Estado::ENVIO_ERROR_GZIP));
                //Debugbar::error("Sii.php ENVIO_ERROR_GZIP");
                return false;
            }
        }
        file_put_contents($file, $dte);
        $data = [
            'rutSender' => $rutSender,
            'dvSender' => $dvSender,
            'rutCompany' => $rutCompany,
            'dvCompany' => $dvCompany,
            'archivo' => curl_file_create(
                $file,
                $gzip ? 'application/gzip' : 'application/xml',
                basename($file)
            ),
        ];
/*
        $headers = [
            'User-Agent'=>'Mozilla/4.0 (compatible; PROG 1.0; PanchoRepuestos)',
            'Referer'=> 'https://www.panchorepuestos.cl',
            'Accept' => 'application/json',
            'Cookie'=> 'TOKEN='.$token
        ];
*/
        $headers = [
            'User-Agent: Mozilla/4.0 (compatible; PROG 1.0; LibreDTE)',
            'Referer: https://www.panchorepuestos.cl',
            'Cookie: TOKEN='.$token,
        ];
        $url=$servidor.'/boleta.electronica.envio';

        // definir reintentos si no se pasaron
        if (!$retry) {
            $retry = self::$retry;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


        try {
            // enviar XML al SII
            for ($i=0; $i<$retry; $i++) {
                $response = curl_exec($curl);
                if ($response && $response!='Error 500') {
                    break;
                }
            }

            unlink($file);
            // verificar respuesta del envío y entregar error en caso que haya uno
            if (!$response || $response=='Error 500') {
                if (!$response) {
                    //\sasco\LibreDTE\Log::write(Estado::ENVIO_ERROR_CURL, Estado::get(Estado::ENVIO_ERROR_CURL, curl_error($curl)));
                    $r=['estado'=>'ERROR','mensaje'=>'No responde servidor del SII'];
                }
                if ($response=='Error 500') {
                    //\sasco\LibreDTE\Log::write(Estado::ENVIO_ERROR_500, Estado::get(Estado::ENVIO_ERROR_500));
                    $r=['estado'=>'ERROR','mensaje'=>'ERROR 500 servidor del SII'];
                }
                $error=true;
            }
            // cerrar sesión curl
            curl_close($curl);
            if($error===false){
                $r=json_decode($response, true);
            }

        } catch (\Exception $e) {
            $r=['estado'=>'ERROR','mensaje'=>'Error envio: '.$e->getMessage()];
        }

        return $r;

/*
        // crear XML con la respuesta y retornar
        try {

            $url='/boleta.electronica.envio';
            $response=Http::withHeaders($headers)
                        ->retry(3,500)
                        //->asForm()
                        //->post('http://gsus.cl/holitas.php',$data);
                        ->post($servidor.$url,$data);
            if($response->successful()){
                $body=$response->getBody();
                //echo $body;
                //$xml = new \SimpleXMLElement($body, LIBXML_COMPACT);
                $estado=['estado'=>'OK','mensaje'=>$body];
            }else if($response->failed()){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo enviar XML (Failed 400)'];
            }else if($response->clientError()){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo enviar XML (Client 400)'];
            }else if($response->serverError()){
                $estado=['estado'=>'ERROR','mensaje'=>'No se pudo enviar XML (Server 500)'];
            }
        } catch (\Exception $e) {

            $estado=['estado'=>'ERRORc','mensaje'=>$e->getMessage()];
        }
        return $estado;
*/

    }

    /**
     * Método para obtener la clave pública (certificado X.509) del SII
     *
     * \code{.php}
     *   $pub_key = \sasco\LibreDTE\Sii::cert(100); // Certificado IDK 100 (certificación)
     * \endcode
     *
     * @param idk IDK de la clave pública del SII. Si no se indica se tratará de determinar con el ambiente que se esté usando
     * @return Contenido del certificado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-16
     */
    public static function cert($idk = null)
    {
        // si se pasó un idk y existe el archivo asociado se entrega
        if ($idk) {
            //$cert = dirname(dirname(__FILE__)).'/certs/'.$idk.'.cer';
            $cert = base_path().'/cert_sii/'.$idk.'.cer';
            if (is_readable($cert)) {
                return file_get_contents($cert);
            }
        }
        // buscar certificado y entregar si existe o =false si no
        $ambiente = self::getAmbiente();
        $cert = base_path().'/cert_sii/'.$idk.'.cer'.self::$config['certs'][$ambiente].'.cer';
        if (!is_readable($cert)) {
            Log::write(Estado::SII_ERROR_CERTIFICADO, Estado::get(Estado::SII_ERROR_CERTIFICADO, self::$config['certs'][$ambiente]));
            return false;
        }
        return file_get_contents($cert);
    }

    /**
     * Método que asigna el ambiente que se usará por defecto (si no está
     * asignado con la constante _LibreDTE_CERTIFICACION_)
     * @param ambiente Ambiente a usar: Sii::PRODUCCION o Sii::CERTIFICACION
     * @warning No se está verificando SSL en ambiente de certificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-28
     */
    public static function setAmbiente($ambiente = self::PRODUCCION)
    {
        $ambiente = $ambiente ? self::CERTIFICACION : self::PRODUCCION;
        if ($ambiente==self::CERTIFICACION) {
            self::setVerificarSSL(false);
        }
        self::$ambiente = $ambiente;
    }

    /**
     * Método que determina el ambiente que se debe utilizar: producción o
     * certificación
     * @param ambiente Ambiente a usar: Sii::PRODUCCION o Sii::CERTIFICACION o null (para detección automática)
     * @return Ambiente que se debe utilizar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-07
     */
    public static function getAmbiente($ambiente = null)
    {
        if ($ambiente===null) {
            if (defined('_LibreDTE_CERTIFICACION_'))
                $ambiente = (int)_LibreDTE_CERTIFICACION_;
            else
                $ambiente = self::$ambiente;
        }
        return $ambiente;
    }

    /**
     * Método que entrega la tasa de IVA vigente
     * @return Tasa de IVA vigente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-03
     */
    public static function getIVA()
    {
        return self::IVA;
    }

    /**
     * Método que entrega la dirección regional según la comuna que se esté
     * consultando
     * @param comuna de la sucursal del emior o bien código de la sucursal del SII
     * @return Dirección regional del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-07
     */
    public static function getDireccionRegional($comuna)
    {
        if (!$comuna) {
            return 'N.N.';
        }
        if (!is_numeric($comuna)) {
            $direccion = mb_strtoupper($comuna, 'UTF-8');
            return isset(self::$direcciones_regionales[$direccion]) ? self::$direcciones_regionales[$direccion] : $direccion;
        }
        return 'SUC '.$comuna;
    }

}
