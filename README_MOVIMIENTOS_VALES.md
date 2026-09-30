# Sistema de Registro de Movimientos - Vales de Mercadería

## Descripción

Este sistema registra automáticamente todos los movimientos de repuestos en vales de mercadería, permitiendo llevar un control detallado del stock y auditoría completa de transacciones.

## Estructura

### 1. **Base de Datos**
**Tabla:** `vale_mercaderia_movimientos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único del registro |
| `vale_mercaderia_id` | bigint | Referencia al vale de mercadería |
| `repuesto_id` | bigint | Referencia al repuesto |
| `usuario_id` | bigint | Usuario que realizó la operación |
| `local_id` | int | Origen del movimiento (1=Bodega, 3=Segunda ubicación, 4=Tercera ubicación) |
| `cantidad` | int | Cantidad de unidades |
| `tipo_movimiento` | enum | Tipo: 'agregar', 'eliminar', 'devolver' |
| `numero_voucher` | string | Número del vale/comprobante |
| `observaciones` | text | Notas adicionales |
| `created_at` | timestamp | Fecha y hora del movimiento |
| `updated_at` | timestamp | Última actualización |

### 2. **Archivos Creados**

#### Migración
```
database/migrations/2026_06_03_create_vale_mercaderia_movimientos_table.php
```
Crea la tabla `vale_mercaderia_movimientos` con todos los índices necesarios.

#### Modelo
```
app/vale_mercaderia_movimiento.php
```
Modelo Eloquent con relaciones y scopes para consultas comunes.

**Métodos útiles:**
- `scopeDelVale()` - Filtrar por vale
- `scopeDelRepuesto()` - Filtrar por repuesto
- `scopePorTipo()` - Filtrar por tipo de movimiento
- `scopeEntreFechas()` - Filtrar por rango de fechas
- `getNombreLocal()` - Obtener nombre del local

#### Helper
```
app/Helpers/MovimientoValeHelper.php
```
Funciones auxiliares para trabajar con movimientos.

**Métodos disponibles:**

##### `registrarMovimiento()`
Registra un movimiento de repuesto.

```php
MovimientoValeHelper::registrarMovimiento(
    $vale_id,           // ID del vale
    $repuesto_id,       // ID del repuesto
    $cantidad,          // Cantidad de unidades
    $tipo_movimiento,   // 'agregar', 'eliminar' o 'devolver'
    $local_id,          // 1, 3 o 4
    $numero_voucher,    // Número del vale (opcional)
    $observaciones      // Notas (opcional)
);
```

##### `historicoRepuesto()`
Obtiene el historial de movimientos de un repuesto en un período.

```php
$movimientos = MovimientoValeHelper::historicoRepuesto(
    $repuesto_id,
    '2026-01-01',  // fecha_inicio
    '2026-12-31'   // fecha_fin
);
```

##### `balanceRepuesto()`
Calcula el balance de stock de un repuesto.

```php
$balance = MovimientoValeHelper::balanceRepuesto(
    $repuesto_id,
    '2026-01-01',
    '2026-12-31'
);

// Retorna:
// [
//     'total_salidas' => 100,
//     'total_entradas' => 150,
//     'balance_neto' => 50,
//     'por_local' => [
//         1 => ['salidas' => 50, 'entradas' => 80],
//         3 => ['salidas' => 30, 'entradas' => 40],
//         4 => ['salidas' => 20, 'entradas' => 30],
//     ]
// ]
```

##### `repuestosMasSacados()`
Obtiene los repuestos más sacados en un período.

```php
$top_repuestos = MovimientoValeHelper::repuestosMasSacados(
    '2026-01-01',
    '2026-12-31',
    10  // Número máximo de registros
);
```

## Instalación

### Paso 1: Ejecutar la migración

```bash
php artisan migrate
```

Esto creará la tabla `vale_mercaderia_movimientos` en la base de datos.

### Paso 2: Usar el sistema

El sistema se activará automáticamente cuando:

1. **Se agrega un repuesto a un vale:** 
   - Se registra movimiento tipo 'agregar'
   - Se disminuye el stock del local origen
   - Se guarda en la tabla de movimientos

2. **Se elimina un repuesto de un vale:**
   - Se registra movimiento tipo 'eliminar'
   - Se devuelve el stock al local origen
   - Se guarda en la tabla de movimientos

## Ejemplos de Uso

### Obtener todos los movimientos de un repuesto
```php
$movimientos = vale_mercaderia_movimiento::delRepuesto(5)->get();
```

### Obtener movimientos de un vale específico
```php
$movimientos = vale_mercaderia_movimiento::delVale(10)->get();
```

### Obtener solo movimientos de salida
```php
$salidas = vale_mercaderia_movimiento::porTipo('eliminar')->get();
```

### Obtener movimientos en un rango de fechas
```php
$fecha_ini = '2026-06-01 00:00:00';
$fecha_fin = '2026-06-30 23:59:59';
$movimientos = vale_mercaderia_movimiento::entreFechas($fecha_ini, $fecha_fin)->get();
```

### Consulta compleja
```php
$movimientos = vale_mercaderia_movimiento::delRepuesto(5)
    ->entreFechas('2026-06-01 00:00:00', '2026-06-30 23:59:59')
    ->porTipo('eliminar')
    ->orderBy('created_at', 'desc')
    ->get();
```

## Reportes Sugeridos

### 1. Historial de Stock por Repuesto
```php
$repuesto_id = 5;
$inicio = '2026-01-01';
$fin = '2026-12-31';

$movimientos = MovimientoValeHelper::historicoRepuesto($repuesto_id, $inicio, $fin);
```

### 2. Balance de Movimientos
```php
$balance = MovimientoValeHelper::balanceRepuesto($repuesto_id, $inicio, $fin);

// Muestra cuánto salió, cuánto entró y el neto
```

### 3. Repuestos Críticos
```php
$top_sacados = MovimientoValeHelper::repuestosMasSacados($inicio, $fin, 20);

// Identifica los repuestos más requeridos en vales
```

## Mantenimiento

### Ver todos los movimientos registrados
```php
$movimientos = vale_mercaderia_movimiento::orderBy('created_at', 'desc')->get();
```

### Buscar movimientos sin vale asociado
```php
$huerfanos = vale_mercaderia_movimiento::whereNull('vale_mercaderia_id')->get();
```

### Limpiar registros antiguos (si es necesario)
```php
$antiguos = vale_mercaderia_movimiento::where('created_at', '<', now()->subMonths(12))->delete();
```

## Integración con Vistas

### Mostrar historial en una vista
```blade
@forelse($movimientos as $mov)
    <tr>
        <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
        <td>{{ $mov->repuesto->codigo_interno ?? 'N/A' }}</td>
        <td>{{ $mov->cantidad }}</td>
        <td>{{ $mov->tipo_movimiento }}</td>
        <td>{{ $mov->getNombreLocal() }}</td>
        <td>{{ $mov->usuario->name ?? 'N/A' }}</td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center">No hay movimientos</td></tr>
@endforelse
```

## Troubleshooting

### La migración no se ejecuta
1. Verificar que `database/migrations/` existe
2. Asegurar que el archivo de migración está en el directorio correcto
3. Ejecutar: `php artisan migrate --path=database/migrations/2026_06_03_create_vale_mercaderia_movimientos_table.php`

### No se registran movimientos
1. Verificar que el Helper se importó correctamente en el controlador
2. Verificar que hay conexión a la base de datos
3. Revisar logs en `storage/logs/`

### Errores de relación
1. Asegurar que los modelos tienen las relaciones `belongsTo` correctas
2. Verificar que los nombres de tablas coinciden con las claves foráneas

## Próximos Pasos Sugeridos

1. **Crear un controlador de reportes:** Para visualizar los datos
2. **Agregar gráficos:** Mostrar tendencias de movimientos
3. **Alertas automáticas:** Notificar cuando stock es bajo
4. **Exportar reportes:** CSV o Excel con históricos de movimientos
5. **Dashboard:** Panel de resumen de movimientos
