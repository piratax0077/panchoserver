# Stock por local

## Alcance

La pantalla `/repuesto/stockrepuesto` usa la nueva gestión cuando `STOCK_POR_LOCAL=true`:
crear/editar/desactivar locales, asociarlos a repuestos, editar posición física y responsable,
ajustar existencias y trasladarlas con historial paginado. Las modificaciones requieren rol
Administrador; los usuarios con permiso de inventario conservan consulta. Disminuir un
stock manualmente mantiene la autorización con clave ya existente.

`repuesto_stocks` guarda una fila por repuesto/local. `StockPorLocalService` aplica las
operaciones en transacciones, bloquea registros, impide saldos negativos y detecta ajustes
hechos sobre un saldo que cambió. Los traslados registran dos movimientos con el mismo UUID.
El historial anterior de `stock_movimientos` se conserva.

Compras individuales/importaciones, descuentos de ventas/carritos y devoluciones aprobadas
usan el servicio con el local seleccionado. Los selectores de ventas y devolución admiten
los nuevos locales. Se adaptaron los totales generales usados por búsquedas, exportación
y reportes intervenidos.

Las columnas antiguas NO se eliminan. Se mantienen como espejos para pantallas antiguas
que siguen mostrando las tres posiciones; sus escrituras Eloquent se sincronizan en la
misma transacción y se rechazan si su lectura quedó obsoleta. Una nueva ubicación puede no
tener columna espejo. Los procesos SQL externos que escriban directamente esas columnas
deben adaptarse al servicio antes de habilitarlo; no pasan por eventos Eloquent.
Los módulos que presentan explícitamente Bodega/Tienda/Casa Matriz conservan su interfaz
durante esta transición; la gestión completa está en la nueva pantalla.

## Diagnóstico de esta instalación (27-09-2026)

Se consultó la base configurada, sin modificar sus existencias ni ejecutar migraciones.
Administración confirmó la correspondencia para stocks SIN local:
primera columna = Bodega (1), segunda = Tienda (3), tercera = Casa Matriz (4).
La copia aplica este criterio; respeta los IDs explícitos existentes.

Después de aplicar ese criterio en la validación:
- 220.448 filas de existencias válidas para copiar.
- 108 repuestos con el mismo local en más de una posición.
- 16 repuestos con stock negativo.
- Reporte: `storage/app/revision_stock_ubicaciones.csv`.

El reporte guarda ID del repuesto, los tres locales, sus cantidades y el problema.
La validación se detiene por cada repuesto en el primer problema encontrado; repetir
el diagnóstico después de corregir los casos detectados.

Los locales repetidos y negativos requieren reconciliación. La migración NO suma
posiciones ambiguas, NO corrige negativos a cero y NO cambia IDs explícitos por suposición.

## Activación

1. Revisar el reporte y reconciliar los casos pendientes con inventario físico/historial.
2. Hacer un respaldo verificable y coordinar mantenimiento y pausa de procesos que escriben
   inventario (incluidas integraciones/colas externas).
3. Validar de nuevo, sin escrituras:

   ```shell
   php artisan stock:migrar-ubicaciones --reporte
   ```

   Debe terminar con cero inconsistencias.

4. En mantenimiento, crear únicamente la nueva estructura. Requiere que exista la
   migración anterior de `stock_movimientos`:

   ```shell
   php artisan down
   php artisan migrate --path=database/migrations/2026_09_27_000000_create_repuesto_stocks_table.php
   php artisan stock:migrar-ubicaciones --aplicar
   ```

   La copia y enlace del historial se ejecutan en una transacción; se comprueba el total.
   El comando se niega a sobrescribir una tabla destino con datos y exige mantenimiento.
   En producción, aplicar el procedimiento habitual de Artisan para confirmar migraciones.

5. Configurar `STOCK_POR_LOCAL=true` en el entorno y refrescar configuración:

   ```shell
   php artisan config:clear
   php artisan up
   ```

6. Probar con usuarios administrador y consulta: búsqueda, cuarto local, ajuste,
   traslado, compra, venta y devolución. Verificar stock y bitácora por local.

No volver a `STOCK_POR_LOCAL=false` después de registrar movimientos en locales nuevos
sin una reconciliación completa: las columnas antiguas no representan todos los locales.
El rollback de la migración rechaza borrar una tabla que ya tiene existencias.

## Verificaciones de desarrollo

```shell
php vendor/phpunit/phpunit/phpunit tests/Unit
php artisan route:list --path=inventario/ubicaciones
```

Las pruebas de stock usan SQLite en memoria; no tocan la base de inventario.
Cubren ubicaciones adicionales, traslado y conservación de total, stock insuficiente,
rollback de una transacción externa, lectura obsoleta, compatibilidad de escrituras,
local inactivo, correspondencia confirmada para campos vacíos, duplicados, totales
y rechazo de escritura por usuarios sin rol administrador.
También se verificó sintaxis PHP, compilación Blade y sintaxis JavaScript de la nueva vista.
No se emitieron documentos de venta ni se ejecutaron operaciones comerciales para probar.
