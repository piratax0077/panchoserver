<?php
namespace App\servicios_sii;
use App\cliente_modelo;
use App\carrito_compra;
use Debugbar;
use Session;

class FormatosDocSii
{
    private static function dameEmisor()
    {
        $Emisor=[
            'RUTEmisor' => str_replace(".","",Session::get('PARAM_RUT')),
            'RznSoc' => Session::get('PARAM_RAZ_SOC'),
            'GiroEmis' => Session::get('PARAM_GIRO'),
            'Acteco' => Session::get('PARAM_GIRO_COD'), //453000 ciuu //503000acteco
            'DirOrigen' => Session::get('PARAM_DOM_MATRIZ'),
            'CmnaOrigen' => Session::get('PARAM_DIR_COMUNA'),
        ];
        return $Emisor;
    }

    public static function dameBoleta($Receptor,$Detalle,$Datos)
    {
        //Obtenemos las referencias
        $Referencia=[];
        foreach($Datos['referencias'] as $referencia){
            $item=array(
                'TpoDocRef' => $referencia[0]->docu,
                'FolioRef' => $referencia[0]->folio,
                'FchRef'=> $referencia[0]->fecha,
               // 'CodRef' => $Datos['CodRef'],
                'RazonRef' => $referencia[0]->razon,
            );
            array_push($Referencia,$item);
        }

        if($Receptor['RUTRecep']=='60803000-K') $Receptor['RUTRecep']='66666666-6';
        $boleta = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $Datos['tipo_dte'],
                    'Folio' => $Datos['folio_dte'],
                ],
                'Emisor' => self::dameEmisor(),
                'Receptor' => $Receptor,
            ],
            'Detalle' =>$Detalle,
            'Referencia' => $Referencia,
        ];

        return $boleta;
    } //fin de boleta

    public static function dameFactura($Receptor,$Detalle,$Datos)
    {
        //Obtenemos las referencias
        $Referencia=[];
        foreach($Datos['referencias'] as $referencia){
            $item=array(
                'TpoDocRef' => $referencia[0]->docu,
                'FolioRef' => $referencia[0]->folio,
                'FchRef'=> $referencia[0]->fecha,
               // 'CodRef' => $Datos['CodRef'],
                'RazonRef' => $referencia[0]->razon,
            );
            array_push($Referencia,$item);
        }

        $factura = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $Datos['tipo_dte'],
                    'Folio' => $Datos['folio_dte'],
                    'MntBruto'=>1,
                    'FmaPago'=>$Datos['FmaPago']
                ],
                'Emisor' => self::dameEmisor(),
                'Receptor' => $Receptor,
            ],
            'Detalle' =>$Detalle,
            'Referencia' => $Referencia,
        ];
        return $factura;
    } //fin de factura


    public static function dameNotaCredito($Receptor,$Detalle,$Datos)
    {
        $notacredito=[
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $Datos['tipo_dte'],
                    'Folio' => $Datos['folio_dte'],
                    'MntBruto'=>1,
                ],
                'Emisor' => self::dameEmisor(),
                'Receptor' =>$Receptor
            ],
            'Detalle' => $Detalle,
            'Referencia' => [
                [
                    'TpoDocRef' => $Datos['TpoDocRef'],
                    'FolioRef' => $Datos['FolioRef'],
                    'FchRef'=> $Datos['FchRef'],
                    'CodRef' => $Datos['CodRef'], //1. anula doc 2.corrige texto 3.corrige montos
                    'RazonRef' => $Datos['RazonRef'],
                ],
            ]
        ];

        return $notacredito;
    } // fin nota de credito


    public static function dameNotaDebito($Receptor,$Detalle,$Datos)
    {
        $notadebito=[
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $Datos['tipo_dte'],
                    'Folio' => $Datos['folio_dte'],
                    'MntBruto'=>1,
                ],
                'Emisor' => self::dameEmisor(),
                'Receptor' =>$Receptor
            ],
            'Detalle' => $Detalle,
            'Referencia' => [
                [
                    'TpoDocRef' => $Datos['TpoDocRef'],
                    'FolioRef' => $Datos['FolioRef'],
                    'FchRef'=> $Datos['FchRef'],
                    'CodRef' => $Datos['CodRef'],
                    'RazonRef' => $Datos['RazonRef'],
                ],
            ]
        ];

        return $notadebito;

    } // fin nota debito

    public static function dameGuiaDespacho($Receptor,$Detalle,$Datos)
    {
        //Obtenemos las referencias
        $Referencia=[];
        foreach($Datos['referencias'] as $referencia){
            $item=array(
                'TpoDocRef' => $referencia[0]->docu,
                'FolioRef' => $referencia[0]->folio,
                'FchRef'=> $referencia[0]->fecha,
               // 'CodRef' => $Datos['CodRef'],
                'RazonRef' => $referencia[0]->razon,
            );
            array_push($Referencia,$item);
        }

        $guiadespacho = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $Datos['tipo_dte'],
                    'Folio' => $Datos['folio_dte'],
                    'TipoDespacho' => $Datos['tipo_despacho']==0 ? false : $Datos['tipo_despacho'],
                    'IndTraslado' => $Datos['tipo_traslado']
                ],
                'Emisor' => self::dameEmisor(),
                'Receptor' => $Receptor,
                'Transporte'=>$Datos['transporte'],
            ],
            'Detalle' =>$Detalle,
            'Referencia' => $Referencia,
        ];
        return $guiadespacho;
    } //fin de Guia despacho

}





?>
