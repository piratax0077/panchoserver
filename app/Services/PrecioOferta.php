<?php
namespace App\Services;
class PrecioOferta
{
    public static function calcular($precio, $tipo, $valor)
    {
        if (!is_numeric($precio) || !is_numeric($valor) || !is_finite((float) $precio) || !is_finite((float) $valor) || $precio <= 0 || $valor <= 0) {
            throw new \InvalidArgumentException('El precio y el descuento deben ser positivos.');
        }
        if (!in_array($tipo, ['monto', 'porcentaje'], true) || ($tipo === 'porcentaje' && $valor >= 100)) {
            throw new \InvalidArgumentException('El porcentaje debe ser mayor a 0 y menor a 100.');
        }
        $precio = (int) round($precio);
        $descuento = (int) round($tipo === 'porcentaje' ? $precio * $valor / 100 : $valor);
        if ($descuento <= 0 || $descuento >= $precio) {
            throw new \InvalidArgumentException('El descuento debe dejar un precio de oferta mayor a cero.');
        }
        return ['descuento' => $descuento, 'precio_actualizado' => $precio - $descuento];
    }
}
