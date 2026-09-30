<?php

namespace App\Services;

class CostosCompra
{
    // compras_det.costos almacena transporte por unidad, no el total de la fila.
    public static function fleteUnitario($valor)
    {
        if (!is_numeric($valor) || !is_finite((float) $valor) || $valor < 0) {
            throw new \InvalidArgumentException('Flete unitario no valido.');
        }

        return round((float) $valor, 2);
    }
}