<?php
namespace App\servicios_sii;
use App\cliente_modelo;
use App\carrito_compra;
use Debugbar;
use Session;

class FormatosDocSii_online
{
    private static function dameEmisor()
    {
        $Emisor=[
            'RUTEmisor' => str_replace(".","","76.881.221-7"),
            'RznSoc' => "JOSE FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES SPA",
            'GiroEmis' => "VENTA DE PARTES, PIEZAS Y ACCESORIOS PARA VEHICULOS AUTOMOTORES.",
            'Acteco' => 453000, //453000 ciuu //503000acteco
            'DirOrigen' => "Casa Matriz: Pasaje Riquelme #831; Sucursal: Vicuña Mackena #1048",
            'CmnaOrigen' => "Arica",
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
