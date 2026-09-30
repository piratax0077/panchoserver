<?php
namespace App\Services;

/**
 * Criterio confirmado por administración:
 * negativos a cero y locales repetidos/vacíos según columna 1=Bodega, 2=Tienda, 3=CM.
 * No se modifican locales explícitos válidos que no están repetidos.
 */
class NormalizadorStockLegado
{
    const LOCALES = [1 => 1, 2 => 3, 3 => 4];

    public function cambios($repuesto): array
    {
        $cambios = [];
        $vistos = [];
        $repetido = false;
        foreach (StockPorLocalService::CAMPOS as $indice => $campos) {
            list($stock, $local) = $campos;
            $cantidad = (int) $repuesto->$stock;
            $localId = (int) $repuesto->$local;
            if ($cantidad < 0) $cambios[$stock] = 0;
            if (!$localId && $cantidad !== 0) {
                $localId = self::LOCALES[$indice];
                $cambios[$local] = $localId;
            }
            if ($localId) {
                if (isset($vistos[$localId])) $repetido = true;
                $vistos[$localId] = true;
            }
        }
        if ($repetido) {
            foreach (StockPorLocalService::CAMPOS as $indice => $campos) {
                if ((int) $repuesto->{$campos[1]} !== self::LOCALES[$indice]) {
                    $cambios[$campos[1]] = self::LOCALES[$indice];
                }
            }
        }
        return $cambios;
    }
}
