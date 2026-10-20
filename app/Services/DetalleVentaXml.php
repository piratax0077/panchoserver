<?php
namespace App\Services;

class DetalleVentaXml
{
    // El XML numera por posicion; item puede tener saltos o repetirse en el carrito.
    public static function vincular($carrito, \SimpleXMLElement $xml)
    {
        $lineas = $xml->xpath('//*[local-name()="Documento"]/*[local-name()="Detalle"]');
        if (count($carrito) === 0 || count($carrito) !== count($lineas)) {
            throw new \RuntimeException('El carrito no coincide con la cantidad de productos del XML. Vuelva a generar el documento.');
        }
        $resultado = [];
        $posicion = 0;
        foreach ($carrito as $item) {
            $linea = $lineas[$posicion++];
            $nombre = function ($valor) {
                return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $valor)), 'UTF-8');
            };
            if ($nombre(mb_substr($item->descripcion, 0, 80)) !== $nombre($linea->NmbItem)
                || (float) $item->cantidad !== (float) $linea->QtyItem
                || !isset($linea->PrcItem) || !isset($linea->MontoItem)
                || isset($resultado[$item->id])) {
                throw new \RuntimeException('El carrito cambio o no coincide con el XML. Vuelva a generar el documento.');
            }
            $resultado[$item->id] = $linea;
        }
        return $resultado;
    }
}