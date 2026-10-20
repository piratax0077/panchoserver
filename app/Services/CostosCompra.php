<?php

namespace App\Services;

class CostosCompra
{
    // MontoItem incluye el descuento de la linea y su redondeo en el XML.
    public static function precioCompraXml($detalle)
    {
        $precio = (float) $detalle->PrcItem;
        $tieneDescuento = (float) $detalle->DescuentoMonto > 0
            || (float) $detalle->DescuentoPct > 0;
        if (!$tieneDescuento) {
            return $precio;
        }

        $cantidad = (float) $detalle->QtyItem;
        if ($cantidad <= 0 || !isset($detalle->MontoItem)) {
            throw new \InvalidArgumentException('Descuento XML sin cantidad o monto de linea valido.');
        }
        return (float) $detalle->MontoItem / $cantidad;
    }

    // compras_det.costos almacena transporte por unidad, no el total de la fila.
    public static function fleteUnitario($valor)
    {
        if (!is_numeric($valor) || !is_finite((float) $valor) || $valor < 0) {
            throw new \InvalidArgumentException('Flete unitario no valido.');
        }

        return round((float) $valor, 2);
    }
}