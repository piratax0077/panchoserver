<?php
namespace App\Services;

class ReconstruccionDetalleBoleta
{
    public static function plan($carrito, $existentes, \SimpleXMLElement $xml)
    {
        $lineas = DetalleVentaXml::vincular($carrito, $xml);
        $usados = [];
        $plan = [];
        foreach ($carrito as $item) {
            $linea = $lineas[$item->id];
            $datos = ['id_repuestos'=>(int)$item->id_repuestos,
                'id_unidad_venta'=>(int)$item->id_unidad_venta,'id_local'=>(int)$item->id_local,
                'precio_venta'=>(float)$linea->PrcItem,'cantidad'=>(float)$linea->QtyItem,
                'subtotal'=>(float)$linea->MontoItem+(float)$linea->DescuentoMonto,
                'descuento'=>(float)$linea->DescuentoMonto,'total'=>(float)$linea->MontoItem];
            if ((float)$item->pu !== $datos['precio_venta'] || (float)$item->total_item !== $datos['total']) {
                throw new \RuntimeException('Los importes del carrito no coinciden con el XML.');
            }
            $encontrado = null;
            foreach ($existentes as $detalle) {
                if (isset($usados[$detalle->id]) || (int)$detalle->id_repuestos !== $datos['id_repuestos']) continue;
                foreach ($datos as $campo=>$valor) {
                    if (abs((float)$detalle->$campo-$valor)>0.01) {
                        throw new \RuntimeException('Un detalle existente difiere del carrito/XML; requiere revision.');
                    }
                }
                $usados[$detalle->id] = true;
                $encontrado = (int)$detalle->id;
                break;
            }
            $plan[] = ['carrito_transferido_id'=>(int)$item->id, 'detalle_existente_id'=>$encontrado,
                'linea_xml'=>(int)$linea->NroLinDet,'datos'=>$datos];
        }
        if (count($usados)!==count($existentes)) {
            throw new \RuntimeException('Hay detalles existentes que no corresponden al carrito/XML.');
        }
        return $plan;
    }
}