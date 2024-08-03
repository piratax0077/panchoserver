<?php
namespace App\servicios_sii;
use Session;
use TCPDF;
use DateTime;

// respuesta en texto plano
//header('Content-type: text/plain; charset=ISO-8859-1');
class ClsAmbiente
{
    public static function dame_firma()
    {
        $clave1="Pan831";
        $clave2="pancho1048";
        $archivo_firma=base_path().'/cert/josetroncoso2023.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
            return $Firma;
        }else{
            return false;
        }

    }

    public static function basico(){
        // primer folio a usar para envio de set de pruebas
        $folios = [
            33 => 9, // factura electrónica
            61 => 7, // nota de crédito electrónicas
            56 => 3, // nota de débito electrónica
        ];

        // caratula para el envío de los dte
        $caratula = [
            'RutEnvia' => '13412179-3',
            'RutReceptor' => '60803000-K',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
        ];

        // datos del emisor
        $Emisor = [
            'RUTEmisor' => '76881221-7',
            'RznSoc' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroEmis' => 'COMPRA Y VENTA DE REPUESTOS DE VEHICULOS MOTORIZADOS',
            'Acteco' => 453000,
            'DirOrigen' => 'Arica',
            'CmnaOrigen' => 'Arica',
        ];

        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
        $set_pruebas = [
            // CASO 1595775-1 FACTURA ELECTRONICA NORMAL
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                        'QtyItem' => 152,
                        'PrcItem' => 2582,
                    ],
                    [
                        'NmbItem' => 'Relleno AFECTO',
                        'QtyItem' => 65,
                        'PrcItem' => 4278,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folios[33],
                    'RazonRef' => 'CASO 1595775-1',
                ],
            ],
            // CASO 1595775-2 FACTURA ELECTRONICA CON DESCUENTOS POR ITEMS
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pañuelo AFECTO',
                        'QtyItem' => 573,
                        'PrcItem' => 4478,
                        'DescuentoPct' => 8,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 511,
                        'PrcItem' => 3533,
                        'DescuentoPct' => 17,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folios[33]+1,
                    'RazonRef' => 'CASO 1595775-2',
                ],
            ],
            // CASO 1595775-3 FACTURA ELECTRONICA CON ITEM EXENTO
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pintura B&W AFECTO',
                        'QtyItem' => 44,
                        'PrcItem' => 5244,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 205,
                        'PrcItem' => 3574,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 1,
                        'PrcItem' => 35084,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folios[33]+2,
                    'RazonRef' => 'CASO 1595775-3',
                ],
            ],
            // CASO 1595775-4 FACTURA ELECTRÓNICA CON DESCUENTO GLOBAL
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+3,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1 AFECTO',
                        'QtyItem' => 296,
                        'PrcItem' => 4416,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 126,
                        'PrcItem' => 5153,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 2,
                        'PrcItem' => 6810,
                    ],
                ],
                'DscRcgGlobal' => [
                    'TpoMov' => 'D',
                    'TpoValor' => '%',
                    'ValorDR' => 17,
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folios[33]+3,
                    'RazonRef' => 'CASO 1595775-4',
                ],
            ],
            // CASO 1595775-5 NOTA CREDITO CORRIGE GIRO RECEPTOR DE CASO 1595775-1
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => $folios[61],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        'MntTotal' => 0,
                    ]
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => $folios[61],
                        'RazonRef' => 'CASO 1595775-5',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => $folios[33],
                        'CodRef' => 2,
                        'RazonRef' => 'CORRIGE GIRO DEL RECEPTOR',
                    ],
                ]
            ],
            // CASO 1595775-6 NOTA CREDITO ELECTRONICA DEVUELVE MERCADERIAS CASO 1595775-2
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => $folios[61]+1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        // estos valores serán calculados automáticamente
                        'MntNeto' => 0,
                        'TasaIVA' => 19,
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pañuelo AFECTO',
                        'QtyItem' => 210,
                        'PrcItem' => 4478, //copiar el precio unitario del caso 2
                        'DescuentoPct' => 8,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 347,
                        'PrcItem' => 3533, //copiar el precio unitario del caso 2
                        'DescuentoPct' => 17,
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => $folios[61]+1,
                        'RazonRef' => 'CASO 1595775-6',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => $folios[33]+1,
                        'CodRef' => 3,
                        'RazonRef' => 'DEVOLUCION DE MERCADERIAS',
                    ],
                ]
            ],
            // CASO 1595775-7 NOTA CREDITO ELECTRONICA ANULA FACTURA CASO 3
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => $folios[61]+2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        // estos valores serán calculados automáticamente
                        'MntNeto' => 0,
                        'MntExe' => 0,
                        'TasaIVA' => 19,
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [//COPIAR CANTIDADES Y PRECIOS DEL CASO 3
                    [
                        'NmbItem' => 'Pintura B&W AFECTO',
                        'QtyItem' => 44,
                        'PrcItem' => 5244,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 205,
                        'PrcItem' => 3574,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 1,
                        'PrcItem' => 35084,
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => $folios[61]+2,
                        'RazonRef' => 'CASO 1595775-7',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => $folios[33]+2,
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA FACTURA',
                    ],
                ]
            ],
            // CASO 1347240-8 NOTA DEBITO ELECTRONICA ANULA NOTA DE CREDITO CASO 5
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 56,
                        'Folio' => $folios[56],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' =>$Receptor,
                    'Totales' => [
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => $folios[56],
                        'RazonRef' => 'CASO 1595775-8',
                    ],
                    [
                        'TpoDocRef' => 61,
                        'FolioRef' => $folios[61],
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA NOTA DE CREDITO ELECTRONICA',
                    ],
                ]
            ],
        ];

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $Folios = [];
        foreach ($folios as $tipo => $cantidad){
            $Folios[$tipo] = new Folios(file_get_contents(base_path().'/xml/folios/'.$tipo.'.xml'));
        }

        $EnvioDTE = new EnvioDte();

        // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
        foreach ($set_pruebas as $documento) {
            $DTE = new Dte($documento);
            if (!$DTE->timbrar($Folios[$DTE->getTipo()]))
                break;
            if (!$DTE->firmar($Firma))
                break;
            $EnvioDTE->agregar($DTE);
        }

        // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($Firma);
        $nombre='set_basico_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
        return "Listo: ".$nombre.'.xml en /xml/generados';
    } // fin de basico

    public static function libroventas(){

        // caratula del libro
        $caratula = [
            'RutEmisorLibro' => '76881221-7',
            'RutEnvia' => '13412179-3',
            'PeriodoTributario' => '2020-11',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 1,
        ];

        // datos del emisor
        $Emisor = [
            'RUTEmisor' => '76881221-7',
            'RznSoc' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroEmis' => 'COMPRA Y VENTA DE REPUESTOS DE VEHICULOS MOTORIZADOS',
            'Acteco' => 453000,
            'DirOrigen' => 'Arica',
            'CmnaOrigen' => 'Arica',
        ];

        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        //CASO ATENCIÓN: 1595776


        // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
        // IMPORTANTE: folios deben coincidir con los de los DTEs que fueron aceptados
        // en el proceso de certificación del set de pruebas básico
       // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
        $set_pruebas = [
            // CASO 1595775-1
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => 1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                        'QtyItem' => 152,
                        'PrcItem' => 2582,
                    ],
                    [
                        'NmbItem' => 'Relleno AFECTO',
                        'QtyItem' => 65,
                        'PrcItem' => 4278,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => 1,
                    'RazonRef' => 'CASO 1595775-1',
                ],
            ],
            // CASO 1595775-2
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => 2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pañuelo AFECTO',
                        'QtyItem' => 573,
                        'PrcItem' => 4478,
                        'DescuentoPct' => 8,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 511,
                        'PrcItem' => 3533,
                        'DescuentoPct' => 17,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => 2,
                    'RazonRef' => 'CASO 1595775-2',
                ],
            ],
            // CASO 1595775-3
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => 3,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pintura B&W AFECTO',
                        'QtyItem' => 44,
                        'PrcItem' => 5244,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 205,
                        'PrcItem' => 3574,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 1,
                        'PrcItem' => 35084,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => 3,
                    'RazonRef' => 'CASO 1595775-3',
                ],
            ],
            // CASO 1595775-4
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => 4,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1 AFECTO',
                        'QtyItem' => 296,
                        'PrcItem' => 4416,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 126,
                        'PrcItem' => 5153,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 2,
                        'PrcItem' => 6810,
                    ],
                ],
                'DscRcgGlobal' => [
                    'TpoMov' => 'D',
                    'TpoValor' => '%',
                    'ValorDR' => 17,
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => 4,
                    'RazonRef' => 'CASO 1595775-4',
                ],
            ],
            // CASO 1595775-5
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => 1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        'MntTotal' => 0,
                    ]
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => 1,
                        'RazonRef' => 'CASO 1595775-5',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => 1,
                        'CodRef' => 2,
                        'RazonRef' => 'CORRIGE GIRO DEL RECEPTOR',
                    ],
                ]
            ],
            // CASO 1595775-6
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => 2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        // estos valores serán calculados automáticamente
                        'MntNeto' => 0,
                        'TasaIVA' => 19,
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pañuelo AFECTO', //kaka
                        'QtyItem' => 210,
                        'PrcItem' => 4478,
                        'DescuentoPct' => 8,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 347,
                        'PrcItem' => 3533,
                        'DescuentoPct' => 17,
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => 2,
                        'RazonRef' => 'CASO 1595775-6',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => 2,
                        'CodRef' => 3,
                        'RazonRef' => 'DEVOLUCION DE MERCADERIAS',
                    ],
                ]
            ],
            // CASO 1595775-7
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => 3,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        // estos valores serán calculados automáticamente
                        'MntNeto' => 0,
                        'MntExe' => 0,
                        'TasaIVA' => 19,
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Pintura B&W AFECTO',
                        'QtyItem' => 44,
                        'PrcItem' => 5244,
                    ],
                    [
                        'NmbItem' => 'ITEM 2 AFECTO',
                        'QtyItem' => 205,
                        'PrcItem' => 3574,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                        'QtyItem' => 1,
                        'PrcItem' => 35084,
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => 3,
                        'RazonRef' => 'CASO 1595775-7',
                    ],
                    [
                        'TpoDocRef' => 33,
                        'FolioRef' => 3,
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA FACTURA',
                    ],
                ]
            ],
            // CASO 1595775-8
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 56,
                        'Folio' => 1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' =>$Receptor,
                    'Totales' => [
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cajón AFECTO',
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 'SET',
                        'FolioRef' => 1,
                        'RazonRef' => 'CASO 1595775-8',
                    ],
                    [
                        'TpoDocRef' => 61,
                        'FolioRef' => 1,
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA NOTA DE CREDITO ELECTRONICA',
                    ],
                ]
            ],
        ];

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $LibroCompraVenta = new LibroCompraVenta();

        // generar cada DTE y agregar su resumen al detalle del libro
        foreach ($set_pruebas as $documento) {
            $DTE = new Dte($documento);
            $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
        }

        // enviar libro de ventas y mostrar resultado del envío: track id o bien =false si hubo error
        $LibroCompraVenta->setCaratula($caratula);
        $LibroCompraVenta->setFirma($Firma);
        $nombre='libro_ventas_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $LibroCompraVenta->generar(false)); // guardar XML en sistema de archivos
        //$LibroCompraVenta->generar(false); // generar XML sin firma y sin detalle
        //$LibroCompraVenta->setFirma($Firma);
        return "Listo: ".$nombre.'.xml en /xml/generados';

    } //fin libroventas

    public static function librocompras(){
        // caratula del libro atencion 1595777
        $caratula = [
            'RutEmisorLibro' => '76881221-7',
            'RutEnvia' => '13412179-3',
            'PeriodoTributario' => '2020-11',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 1,
        ];

        // EN FACTURA CON IVA USO COMUN CONSIDERE QUE EL FACTOR DE PROPORCIONALIDAD
        // DEL IVA ES DE 0.60
        $factor_proporcionalidad_iva = 60; // se divide por 100 al agregar al resumen del período

        // set de pruebas compras - número de atención 1347242
        $detalles = [
            // FACTURA DEL GIRO CON DERECHO A CREDITO
            [
                'TpoDoc' => 30,
                'NroDoc' => 234,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-01',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 43931,
            ],
            // FACTURA DEL GIRO CON DERECHO A CREDITO
            [
                'TpoDoc' => 33,
                'NroDoc' => 32,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-01',
                'RUTDoc' => '78885550-8',
                'MntExe' => 10111,
                'MntNeto' => 10031,
            ],
            // FACTURA CON IVA USO COMUN
            [
                'TpoDoc' => 30,
                'NroDoc' => 781,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-02',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 30058,
                // Al existir factor de proporcionalidad se calculará el IVAUsoComun.
                // Se calculará como MntNeto * (TasaImp/100) y se añadirá a MntIVA.
                // Se quitará del detalle al armar los totales, ya que no es nodo del detalle en el XML.
                'FctProp' => $factor_proporcionalidad_iva,
            ],
            // NOTA DE CREDITO POR DESCUENTO A FACTURA 234
            [
                'TpoDoc' => 60,
                'NroDoc' => 451,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-03',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 2867,
            ],
            // ENTREGA GRATUITA DEL PROVEEDOR
            [
                'TpoDoc' => 33,
                'NroDoc' => 67,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-04',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 11520,
                'IVANoRec' => [
                    'CodIVANoRec' => 4,
                    'MntIVANoRec' => round(11520 * (19/100)),
                ],
            ],
            // COMPRA CON RETENCION TOTAL DEL IVA
            [
                'TpoDoc' => 46,
                'NroDoc' => 9,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-05',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 10323,
                'OtrosImp' => [
                    'CodImp' => 15,
                    'TasaImp' => 19,
                    'MntImp' => round(10323 * (19/100)),
                ],
            ],
            // NOTA DE CREDITO POR DESCUENTO FACTURA ELECTRONICA 32
            [
                'TpoDoc' => 60,
                'NroDoc' => 211,
                'TasaImp' => 19,
                'FchDoc' => $caratula['PeriodoTributario'].'-06',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 7715,
            ],
        ];

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $LibroCompraVenta = new LibroCompraVenta();

        // agregar cada uno de los detalles al libro
        foreach ($detalles as $detalle) {
            $LibroCompraVenta->agregar($detalle);
        }

        // enviar libro de compras y mostrar resultado del envío: track id o bien =false si hubo error
        $LibroCompraVenta->setCaratula($caratula);
        $LibroCompraVenta->setFirma($Firma);
        //$LibroCompraVenta->generar(); // generar XML sin firma
        $nombre='libro_compras_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $LibroCompraVenta->generar()); // guardar XML en sistema de archivos
        return "Listo: ".$nombre.'.xml en /xml/generados';
    }

    public static function setguias(){
        //NUMERO DE ATENCIÓN: 1595778

        $folio_inicial=10;
        // caratula para el envío de los dte:
        $caratula = [
            'RutEnvia' => '13412179-3',
            'RutReceptor' => '60803000-K',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
        ];

        // datos del emisor
        $Emisor = [
            'RUTEmisor' => '76881221-7',
            'RznSoc' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroEmis' => 'COMPRA Y VENTA DE REPUESTOS DE VEHICULOS MOTORIZADOS',
            'Acteco' => 453000,
            'DirOrigen' => 'Arica',
            'CmnaOrigen' => 'Arica',
        ];

        $Receptor_Empresa=[
            'RUTRecep' => '76881221-7',
            'RznSocRecep' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroRecep' => 'Venta Repuestos Automotrices',
            'DirRecep' => 'Arica',
            'CmnaRecep' => 'Arica'
        ];

        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
        $set_pruebas = [
            // CASO 1595778-1: TRASLADO DE MATERIALES ENTRE BODEGAS DE LA EMPRESA
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 52,
                        'Folio' => $folio_inicial,
                        'IndTraslado' => 5
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor_Empresa
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1',
                        'QtyItem' => 73
                    ],
                    [
                        'NmbItem' => 'ITEM 2',
                        'QtyItem' => 106
                    ],
                    [
                        'NmbItem' => 'ITEM 3',
                        'QtyItem' => 69
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folio_inicial,
                    'RazonRef' => 'CASO 1595778-1',
                ],
            ],
            // CASO 1595778-2: VENTA TRASLADO POR: 	EMISOR DEL DOCUMENTO AL LOCAL DEL CLIENTE
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 52,
                        'Folio' => $folio_inicial+1,
                        'TipoDespacho' => 2,
                        'IndTraslado' => 1
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1',
                        'QtyItem' => 279,
                        'PrcItem' => 5850
                    ],
                    [
                        'NmbItem' => 'ITEM 2',
                        'QtyItem' => 537,
                        'PrcItem' => 1457
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folio_inicial+1,
                    'RazonRef' => 'CASO 1595778-2',
                ],
            ],
            // CASO 1595778-3 : VENTA TRASLADO POR: 	CLIENTE
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 52,
                        'Folio' => $folio_inicial+2,
                        'TipoDespacho' => 1,
                        'IndTraslado' => 1
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1',
                        'QtyItem' => 148,
                        'PrcItem' => 1746
                    ],
                    [
                        'NmbItem' => 'ITEM 2',
                        'QtyItem' => 337,
                        'PrcItem' => 4586
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 'SET',
                    'FolioRef' => $folio_inicial+2,
                    'RazonRef' => 'CASO 1595778-3'
                ],
            ]
        ];

        $EnvioDTE = new EnvioDte();

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $Folios=new Folios(file_get_contents(base_path().'/xml/folios/52.xml'));
        // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
        foreach ($set_pruebas as $documento) {
            $DTE = new Dte($documento);
            if (!$DTE->timbrar($Folios))
                break;
            if (!$DTE->firmar($Firma))
                break;
            $EnvioDTE->agregar($DTE);
        }

        // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($Firma);
        $nombre='set_guia_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
        return "Listo: ".$nombre.'.xml en /xml/generados';

    } // fin setguias


    public static function libroguias(){
       $folio_inicial=1;
        // caratula del libro
        $caratula = [
            'RutEmisorLibro' => '76881221-7',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
            'FolioNotificacion' => 1,
        ];

        // receptor de las guías
        $receptor = '60803000-K';

        // set de pruebas guías - número de atención 1595779
        $detalles = [
            // CASO 1
            [
                'Folio' => $folio_inicial,
                'TpoOper' => 5,
                'RUTDoc' => $caratula['RutEmisorLibro'],
                'TasaImp' => 19,
            ],
            // CASO 2 CORRESPONDE A UNA GUIA QUE SE FACTURO EN EL PERIODO
            [
                'Folio' => $folio_inicial+1,
                'TpoOper' => 1,
                'RUTDoc' => $receptor,
                'MntNeto' => 2414559, //(279*5850)+(537*1457)
                'TasaImp' => 19,
                'TpoDocRef' => 33,
                'FolioDocRef' => 2, //no se x q le puse este valor 69.
                'FchDocRef' => date('Y-m-d'),
            ],
            // CASO 3 CORRESPONDE A UNA GUIA ANULADA
            [
                'Folio' => $folio_inicial+2,
                'Anulado' => 2,
                'TpoOper' => 1,
                'RUTDoc' => $receptor,
                'MntNeto' => 1803890,
                'TasaImp' => 19,
            ],
        ];

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $LibroGuia = new LibroGuia();

        // agregar cada uno de los detalles al libro
        foreach ($detalles as $detalle) {
            $LibroGuia->agregar($detalle);
        }

        // enviar libro de guías y mostrar resultado del envío: track id o bien =false si hubo error
        $LibroGuia->setFirma($Firma);
        $LibroGuia->setCaratula($caratula);
        $nombre='libro_guias_despacho_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $LibroGuia->generar()); // guardar XML en sistema de archivos
        return "Listo: ".$nombre.'.xml en /xml/generados';

    } // fin libroguias

    public static function simulacion(){
        // primer folio a usar para envio de simulación
        $folios = [
            33 => 23, // factura electrónica
            61 => 11, // nota de crédito electrónicas
            52 => 8, // guia de despacho
            56 => 5, // nota de debito
        ];

        // caratula para el envío de los dte
        $caratula = [
            'RutEnvia' => '13412179-3',
            'RutReceptor' => '60803000-K',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
        ];

        // datos del emisor
        $Emisor = [
            'RUTEmisor' => '76881221-7',
            'RznSoc' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroEmis' => 'COMPRA Y VENTA DE REPUESTOS DE VEHICULOS MOTORIZADOS',
            'Acteco' => 453000,
            'DirOrigen' => 'Arica',
            'CmnaOrigen' => 'Arica',
        ];

        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        // datos de los DTE (cada elemento del arreglo $documentos es un DTE)
        $documentos = [
            // 1 - Factura: 8204
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cigueñal',
                        'QtyItem' => 1,
                        'PrcItem' => 130252,
                    ],
                    [
                        'NmbItem' => 'Metal Biela',
                        'QtyItem' => 1,
                        'PrcItem' => 6723,
                    ],
                    [
                        'NmbItem' => 'Metal Bancada',
                        'QtyItem' => 1,
                        'PrcItem' => 10924,
                    ],
                    [
                        'NmbItem' => 'Damper',
                        'QtyItem' => 1,
                        'PrcItem' => 20168,
                    ],
                ],
            ],
            // 2 - Factura: 8203
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Kit embrague',
                        'QtyItem' => 1,
                        'PrcItem' => 58824,
                    ],
                    [
                        'NmbItem' => 'Bomba agua',
                        'QtyItem' => 1,
                        'PrcItem' => 20168,
                    ],
                    [
                        'NmbItem' => 'Silicona',
                        'QtyItem' => 1,
                        'PrcItem' => 2941,
                    ],
                ],
            ],
            // 3 - Factura: 8202
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Barra Tensora',
                        'QtyItem' => 2,
                        'PrcItem' => 16807,
                    ],
                    [
                        'NmbItem' => 'Bieletas',
                        'QtyItem' => 2,
                        'PrcItem' => 3783,
                    ],
                    [
                        'NmbItem' => 'Bujes Barra',
                        'QtyItem' => 4,
                        'PrcItem' => 2100,
                    ],
                ],
            ],
            //4 - Factura: 8201
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+3,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Bandeja',
                        'QtyItem' => 1,
                        'PrcItem' => 28572,
                    ],
                ],
            ],
            //5 - Factura: 8200
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+4,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Bateria 90ah',
                        'QtyItem' => 1,
                        'PrcItem' => 48740,
                    ],
                ],
            ],
            //6 - Factura: 8125
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+5,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Amortiguador',
                        'QtyItem' => 4,
                        'PrcItem' => 31513,
                    ],
                ],
            ],
            //7 - nota de crédito:
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 61,
                        'Folio' => $folios[61],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                    'Totales' => [
                        'MntNeto' => 0,
                        'TasaIVA' => 19,
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Amortiguador',
                        'QtyItem' => 4,
                        'PrcItem' => 31513,
                    ],
                ],
                'Referencia' => [
                    'TpoDocRef' => 33,
                    'FolioRef' => $folios[33]+5,
                    'CodRef' => 1,
                    'RazonRef' => 'Anula factura',
                ]
            ],
            //8 - Factura: 8199
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+6,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Juego Bujias',
                        'QtyItem' => 1,
                        'PrcItem' => 40337,
                    ],
                    [
                        'NmbItem' => 'Rot Superior',
                        'QtyItem' => 2,
                        'PrcItem' => 10084,
                    ],
                    [
                        'NmbItem' => 'Rot Inferior',
                        'QtyItem' => 2,
                        'PrcItem' => 12185,
                    ],
                    [
                        'NmbItem' => 'Articulacion Axial',
                        'QtyItem' => 2,
                        'PrcItem' => 8403,
                    ],
                    [
                        'NmbItem' => 'Terminal Dirección',
                        'QtyItem' => 2,
                        'PrcItem' => 7143,
                    ],
                ],
            ],
            //9 - Factura: 8198
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+7,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Solenoide Porter',
                        'QtyItem' => 1,
                        'PrcItem' => 9664,
                    ],
                ],
            ],
            //10 - Factura: 8197
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+8,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Bujias',
                        'QtyItem' => 1,
                        'PrcItem' => 17227,
                    ],
                    [
                        'NmbItem' => 'Switch Hazzard',
                        'QtyItem' => 1,
                        'PrcItem' => 10924,
                    ],
                ],
            ],
            //11 - Factura: 8196
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folios[33]+9,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Alternador',
                        'QtyItem' => 1,
                        'PrcItem' => 109244,
                    ],
                ],
            ],
            [ // 1 - Guía despacho
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 52,
                        'Folio' => $folios[52],
                        'TipoDespacho' => 1,
                        'IndTraslado' => 1
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'ITEM 1',
                        'QtyItem' => 89,
                        'PrcItem' => 1148
                    ],
                    [
                        'NmbItem' => 'ITEM 2',
                        'QtyItem' => 116,
                        'PrcItem' => 1835
                    ],
                ],
            ],
            // 2 - Nota Debito
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 56,
                        'Folio' => $folios[56],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' =>$Receptor,
                    'Totales' => [
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Amortiguador',
                        'QtyItem' => 4,
                        'PrcItem' => 31513,
                    ],
                ],
                'Referencia' => [
                    [
                        'TpoDocRef' => 61,
                        'FolioRef' => $folios[61],
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA NOTA DE CREDITO ELECTRONICA',
                    ],
                ]
            ],
        ];

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $Folios = [];
        foreach ($folios as $tipo => $cantidad)
            $Folios[$tipo] = new Folios(file_get_contents(base_path().'/xml/folios/'.$tipo.'.xml'));

        $EnvioDTE = new EnvioDte();

        // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
        foreach ($documentos as $documento) {
            $DTE = new Dte($documento);
            if (!$DTE->timbrar($Folios[$DTE->getTipo()]))
                break;
            if (!$DTE->firmar($Firma))
                break;
            $EnvioDTE->agregar($DTE);
        }

        // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($Firma);
        $nombre='simulacion_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
        return "Listo: ".$nombre.'.xml en /xml/generados';
    } //fin de simulacion

    public static function intercambio()
    {
        //INTERCAMBIO 1
        // datos para validar
        //$archivo_recibido = base_path().'/xml/bandeja_entrada/ENVIO_DTE_1452162.xml';
        $archivo_recibido = base_path().'/xml/bandeja_entrada/ENVIO_DTE_1596123.xml';

        $RutReceptor_esperado = '76881221-7';
        $RutEmisor_esperado = '88888888-8';

        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new EnvioDte();
        $EnvioDte->loadXML(file_get_contents($archivo_recibido));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();

        // caratula
        $caratula = [
            'RutResponde' => $RutReceptor_esperado,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => 1,
            //'NmbContacto' => '',
            //'MailContacto' => '',
        ];

        // procesar cada DTE
        $RecepcionDTE = [];
        foreach ($Documentos as $DTE) {
            $estado = $DTE->getEstadoValidacion(['RUTEmisor'=>$RutEmisor_esperado, 'RUTRecep'=>$RutReceptor_esperado]);
            $RecepcionDTE[] = [
                'TipoDTE' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'EstadoRecepDTE' => $estado,
                'RecepDTEGlosa' => RespuestaEnvio::$estados['documento'][$estado],
            ];
        }

        // armar respuesta de envío
        $estado = $EnvioDte->getEstadoValidacion(['RutReceptor'=>$RutReceptor_esperado]);
        $RespuestaEnvio = new RespuestaEnvio();
        $RespuestaEnvio->agregarRespuestaEnvio([
            'NmbEnvio' => basename($archivo_recibido),
            'CodEnvio' => 1,
            'EnvioDTEID' => $EnvioDte->getID(),
            'Digest' => $EnvioDte->getDigest(),
            'RutEmisor' => $EnvioDte->getEmisor(),
            'RutReceptor' => $EnvioDte->getReceptor(),
            'EstadoRecepEnv' => $estado,
            'RecepEnvGlosa' => RespuestaEnvio::$estados['envio'][$estado],
            'NroDTE' => count($RecepcionDTE),
            'RecepcionDTE' => $RecepcionDTE,
        ]);

        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula($caratula);

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $RespuestaEnvio->setFirma($Firma);

        // generar, validar y guardar XML
        $xml=$RespuestaEnvio->generar();
        if ($RespuestaEnvio->schemaValidate()) {
            $nombre1='intercambio1_'.intval(microtime(true));
            file_put_contents(base_path().'/xml/generados/'.$nombre1.'.xml', $xml); // guardar XML en sistema de archivos
        }else{
            return "Error en Intercambio1";
        }



        /* ***************************************** INTERCAMBIO 2 */
        // datos
        $RutResponde = '76881221-7';
        $RutFirma = '12345678-9'; //Es de quien recibe la mercadería en la bodega

        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        /*
        $EnvioDte = new EnvioDte();
        $EnvioDte->loadXML(file_get_contents($archivo_recibido));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();
*/
        // caratula
        $caratula = [
            'RutResponde' => $RutResponde,
            'RutRecibe' => $Caratula['RutEmisor'],
            //'NmbContacto' => '',
            //'MailContacto' => '',
        ];

        // objeto EnvioRecibo, asignar carátula y Firma
        $EnvioRecibos = new EnvioRecibos();
        $EnvioRecibos->setCaratula($caratula);

        //Usa la misma Firma de arriba

        $EnvioRecibos->setFirma($Firma);

        // procesar cada DTE
        foreach ($Documentos as $DTE) {
            $EnvioRecibos->agregar([
                'TipoDoc' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'Recinto' => 'Oficina central',
                'RutFirma' => $RutFirma,
            ]);
        }

        // generar, validar y guardar XML
        $xml=$EnvioRecibos->generar();
        if ($EnvioRecibos->schemaValidate()) {
            $nombre2="intercambio2_".intval(microtime(true));
            file_put_contents(base_path().'/xml/generados/'.$nombre2.'.xml', $xml); // guardar XML en sistema de archivos
        }else{
            return "Error en Intercambio2";
        }



        /* ***************************************** INTERCAMBIO 3 */

        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new EnvioDte();
        $EnvioDte->loadXML(file_get_contents($archivo_recibido));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();

        // caratula
        $caratula = [
            'RutResponde' => $RutReceptor_esperado,
            'RutRecibe' => $Caratula['RutEmisor'],
            'IdRespuesta' => 1,
            //'NmbContacto' => '',
            //'MailContacto' => '',
        ];

        // objeto para la respuesta
        $RespuestaEnvio = new RespuestaEnvio();

        // procesar cada DTE
        $i = 1;
        foreach ($Documentos as $DTE) {
            $estado = !$DTE->getEstadoValidacion(['RUTEmisor'=>$RutEmisor_esperado, 'RUTRecep'=>$RutReceptor_esperado]) ? 0 : 2;
            $RespuestaEnvio->agregarRespuestaDocumento([
                'TipoDTE' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'CodEnvio' => $i++,
                'EstadoDTE' => $estado,
                'EstadoDTEGlosa' => RespuestaEnvio::$estados['respuesta_documento'][$estado],
            ]);
        }

        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula($caratula);
        $RespuestaEnvio->setFirma($Firma);

        // generar, validar y guardar XML
        $xml=$RespuestaEnvio->generar();
        if ($RespuestaEnvio->schemaValidate()) {
            $nombre3='intercambio3_'.intval(microtime(true));
            file_put_contents(base_path().'/xml/generados/'.$nombre3.'.xml', $xml); // guardar XML en sistema de archivos
        }else{
            return "Error en Intercambio3";
        }

        return "Archivos de Intercambio Generados: ".$nombre1.PHP_EOL.$nombre2.PHP_EOL.$nombre3;
    } // fin intercambio

    public static function basico_boletas(){
        // primer folio a usar para envio de set de pruebas
        $folios = [
            39 => 25, // boleta electrónica
        ];

        // caratula para el envío de los dte
        $caratula = [
            'RutEnvia' => '13412179-3',
            'RutReceptor' => '60803000-K',
            'FchResol' => '2020-11-01',
            'NroResol' => 0,
        ];

        // datos del emisor
        $Emisor = [
            'RUTEmisor' => '76881221-7',
            'RznSoc' => 'JOSÉ FRANCISCO TRONCOSO TRONCOSO REPUESTOS AUTOMOTRICES E.I.R.L.',
            'GiroEmis' => 'COMPRA Y VENTA DE REPUESTOS DE VEHICULOS MOTORIZADOS',
            'Acteco' => 453000,
            'DirOrigen' => 'Arica',
            'CmnaOrigen' => 'Arica',
        ];

        // datos el recepor
        $Receptor=[
            'RUTRecep' => '60803000-K',
            'RznSocRecep' => 'Servicio Impuestos Internos',
            'GiroRecep' => 'Estado',
            'DirRecep' => 'Santiago',
            'CmnaRecep' => 'Santiago'
        ];

        // datos de las boletas (cada elemento del arreglo $set_pruebas es una boleta)
        $set_pruebas = [
            // CASO 1
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folios[39],
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Cambio de aceite',
                        'QtyItem' => 1,
                        'PrcItem' => 19900,
                    ],
                    [
                        'NmbItem' => 'Alineacion y balanceo',
                        'QtyItem' => 1,
                        'PrcItem' => 9900,
                    ],
                ],
                'Referencia' => [
                    'CodRef' => 'SET',
                    'RazonRef' => 'CASO-1',
                ]
            ],
            // CASO 2
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folios[39]+1,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Papel de regalo',
                        'QtyItem' => 17,
                        'PrcItem' => 120,
                    ],
                ],
                'Referencia' => [
                    'CodRef' => 'SET',
                    'RazonRef' => 'CASO-2',
                ]
            ],
            // CASO 3
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folios[39]+2,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Sandwic',
                        'QtyItem' => 2,
                        'PrcItem' => 1500,
                    ],
                    [
                        'NmbItem' => 'Bebida',
                        'QtyItem' => 2,
                        'PrcItem' => 550,
                    ],
                ],
                'Referencia' => [
                    'CodRef' => 'SET',
                    'RazonRef' => 'CASO-3',
                ]
            ],
            // CASO 4
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folios[39]+3,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'item afecto 1',
                        'QtyItem' => 8,
                        'PrcItem' => 1590,
                    ],
                    [
                        'IndExe' => 1,
                        'NmbItem' => 'item exento 2',
                        'QtyItem' => 2,
                        'PrcItem' => 1000,
                    ],
                ],
                'Referencia' => [
                    'CodRef' => 'SET',
                    'RazonRef' => 'CASO-4',
                ]
            ],
            // CASO 5
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folios[39]+4,
                    ],
                    'Emisor' => $Emisor,
                    'Receptor' => $Receptor,
                ],
                'Detalle' => [
                    [
                        'NmbItem' => 'Arroz',
                        'QtyItem' => 5,
                        'UnmdItem' => 'Kg',
                        'PrcItem' => 700,
                    ],
                ],
                'Referencia' => [
                    'CodRef' => 'SET',
                    'RazonRef' => 'CASO-5',
                ]
            ],

        ];

        // Objetos de Firma y Folios
        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }

        $Folios = [];
        foreach ($folios as $tipo => $cantidad)
            $Folios[$tipo] = new Folios(file_get_contents(base_path().'/xml/folios/'.$tipo.'.xml'));

        // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioBOLETA
        $EnvioDTE = new EnvioDte();
        foreach ($set_pruebas as $documento) {
            $DTE = new Dte($documento);
            if (!$DTE->timbrar($Folios[$DTE->getTipo()]))
                break;
            if (!$DTE->firmar($Firma))
                break;
            $EnvioDTE->agregar($DTE);
        }
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($Firma);

        $xml=$EnvioDTE->generar();
        if ($EnvioDTE->schemaValidate()) {
            $nombre='basico_boletas_'.intval(microtime(true));
            file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $xml); // guardar XML en sistema de archivos
            return "Listo: ".$nombre.'.xml en /xml/generados';
            /*
            if (is_writable('xml/EnvioBOLETA.xml'))
                file_put_contents('xml/EnvioBOLETA.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
            echo $EnvioDTE->generar();
            */
        }else{
            return"XUXA, no validó esquema básico boletas";
        }
    }

    public static function rcof_boletas(){
        // archivos
        $boletas = base_path().'/xml/generados/basico_boletas_1604646904.xml';//OJO: Cambiar al archivo xml del set boletas

        // cargar XML boletas y notas
        $EnvioDTE = new EnvioDte();
        $EnvioDTE->loadXML(file_get_contents($boletas));

        // crear objeto para consumo de folios
        $ConsumoFolio = new ConsumoFolio();

        // Objetos de Firma, Folios y EnvioDTE
        $Firma=self::dame_firma();
        if($Firma===false){
            return "archivo de firma errado.";
        }


//        $ConsumoFolio->setDocumentos([39, 41, 61]); // [39, 61] si es sólo afecto, [41, 61] si es sólo exento
        $ConsumoFolio->setDocumentos([39]);

        // agregar detalle de boletas
        foreach ($EnvioDTE->getDocumentos() as $Dte) {
            $ConsumoFolio->agregar($Dte->getResumen());
        }


        // crear carátula para el envío (se hace después de agregar los detalles ya que
        // así se obtiene automáticamente la fecha inicial y final de los documentos)
        $Caratula = $EnvioDTE->getCaratula();
        $ConsumoFolio->setCaratula([
            'RutEmisor' => $Caratula['RutEmisor'],
            'RutEnvia' => $Caratula['RutEnvia'],
            'FchResol' => $Caratula['FchResol'],
            'NroResol' => $Caratula['NroResol'],
            'SecEnvio'=>4,  //Es las veces que se envía un RCOF en el mismo período
        ]);
        $ConsumoFolio->setFirma($Firma);
        // generar, validar schema y mostrar XML
        $xml=$ConsumoFolio->generar();
        $nombre='rcof_setboletas_'.intval(microtime(true));
        file_put_contents(base_path().'/xml/generados/'.$nombre.'.xml', $xml); // guardar XML en sistema de archivos
        return "RCOF Boletas LISTO...";
/*
        if ($ConsumoFolio->schemaValidate()) {
            file_put_contents(base_path().'/xml/generados/rcof_setboletas_'.intval(microtime(true)).'.xml', $xml); // guardar XML en sistema de archivos
            Debugbar::info("RCOF Boletas LISTO...");
        }else{
            Debugbar::error("xuxa... no validó esquema ConsumoFolio");
        }
*/
    }
    public static function generarPDF(){
        // sin límite de tiempo para generar documentos
        set_time_limit(0);

        // archivo XML de EnvioDTE que se generará
        //$archivo = base_path().'/xml/generados/set_basico_1604263403.xml'; //set basico
        //$archivo = base_path().'/xml/generados/simulacion_1604272454.xml'; //simulación
        //$archivo = base_path().'/xml/generados/set_guia_1604268461.xml'; //set guia 4 5 6
        //$archivo = base_path().'/xml/generados/set_guia_1604257934.xml'; //set guia 1 2 3 este es OK
        $archivo = base_path().'/xml/generados/facturas/33_1346.xml';


        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new EnvioDte();
        $EnvioDte->loadXML(file_get_contents($archivo));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();

        // directorio temporal para guardar los PDF (home/.cagefs/tmp en www.panchorepuestos.cl)
        $dir = sys_get_temp_dir().'/dte_'.$Caratula['RutEmisor'].'_'.$Caratula['RutReceptor'].'_'.str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
        if (is_dir($dir))
            File::rmdir($dir);
        if (!mkdir($dir))
            return 'No fue posible crear directorio temporal para DTEs';

        // procesar cada DTEs e ir agregándolo al PDF
        foreach ($Documentos as $DTE) {
            if (!$DTE->getDatos())
                die('No se pudieron obtener los datos del DTE');

            $pdf = new DtePDF(true); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
            $pdf->setFooterText();

            //$pdf->setLogo('/home/delaf/www/localhost/dev/pages/sasco/website/webroot/img/logo_mini.png'); // debe ser PNG!
            /* logo solo para tamaño carta
            $logo=asset('storage/'.Session::get('PARAM_LOGO'));
            $pdf->setLogo($logo);
            */
            $pdf->setResolucion(['FchResol'=>$Caratula['FchResol'], 'NroResol'=>$Caratula['NroResol']]);
            $pdf->setCedible(true);
            $pdf->agregar($DTE->getDatos(), $DTE->getTED());
            $pdf->Output($dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.pdf', 'F');
        }

        // entregar archivo comprimido que incluirá cada uno de los DTEs
        File::compress($dir, ['format'=>'zip', 'delete'=>true, 'download'=>false]);
        return "Terminó la generación de PDF's en ".$dir;
    }

    public static function pdfcito(){
        // DESPUES $pdf=new DtePDF(true); // papel continuo con true...
        $pdf=new TCPDF();
        $pdf->AddPage();
        $pdf->Text(90, 140, 'This is a test');
        $filename = storage_path() . '/test.pdf';
        $pdf->output($filename, 'F');
        //return Response::download($filename);
        return "yupi";

        $html = <<<EOD
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
EOD;
    //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    //$pdf->Output('example_001.pdf', 'I');

    }
}




