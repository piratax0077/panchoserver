# 📋 Instrucciones de Implementación - Sistema de Movimientos de Vales

## ✅ Archivos Creados

Se han creado los siguientes archivos para el sistema de registro de movimientos:

### 1️⃣ **Migración**
- `database/migrations/2026_06_03_create_vale_mercaderia_movimientos_table.php`

### 2️⃣ **Modelo**
- `app/vale_mercaderia_movimiento.php`

### 3️⃣ **Helper (Utilidades)**
- `app/Helpers/MovimientoValeHelper.php`

### 4️⃣ **Controlador** 
- `app/Http/Controllers/MovimientoValeController.php`

### 5️⃣ **Documentación**
- `README_MOVIMIENTOS_VALES.md` - Guía completa de uso
- `RUTAS_MOVIMIENTOS_VALES.php` - Rutas a agregar

---

## 🚀 Pasos de Implementación

### **Paso 1: Ejecutar la Migración**

```bash
php artisan migrate
```

Esto creará la tabla `vale_mercaderia_movimientos` en tu base de datos.

**Verificar:**
```bash
php artisan migrate:status
```

### **Paso 2: Agregar las Rutas**

Abre el archivo `routes/web.php` y agrega al final:

```php
// Rutas para reportes de movimientos de vales de mercadería
Route::middleware(['auth'])->group(function () {
    
    Route::prefix('movimientos')->group(function () {
        Route::get('/', 'MovimientoValeController@index')->name('movimientos.index');
        Route::get('/repuesto/{repuesto_id}', 'MovimientoValeController@historicoRepuesto')->name('movimientos.historico_repuesto');
        Route::get('/vale/{vale_id}', 'MovimientoValeController@historicoVale')->name('movimientos.historico_vale');
        Route::get('/reportes/repuestos-sacados', 'MovimientoValeController@repuestosMasSacados')->name('movimientos.repuestos_sacados');
        Route::get('/reportes/por-local', 'MovimientoValeController@movimientosPorLocal')->name('movimientos.por_local');
        Route::get('/dashboard', 'MovimientoValeController@dashboard')->name('movimientos.dashboard');
        Route::get('/exportar', 'MovimientoValeController@exportar')->name('movimientos.exportar');
    });
});
```

### **Paso 3: Verificar el Controlador**

✅ El controlador `ventas_controlador.php` ya ha sido modificado para:
- Importar el Helper de movimientos
- Registrar movimientos cuando se agregan repuestos (función `agregar_repuesto_valemercaderia`)
- Registrar movimientos cuando se eliminan repuestos (función `eliminar_repuesto_valemercaderia`)
- **CORRECCIÓN IMPORTANTE:** Ahora devuelve el stock cuando se elimina un repuesto

---

## 🆕 Cambios Realizados en el Controlador

### En `app/Http/Controllers/ventas_controlador.php`:

**Importaciones agregadas (línea ~75):**
```php
use App\Helpers\MovimientoValeHelper;
use App\vale_mercaderia_movimiento;
```

**Función mejorada: `agregar_repuesto_valemercaderia()`**
- Ahora registra automáticamente cada repuesto agregado al vale
- Registra: usuario, cantidad, local origen, fecha/hora

**Función corregida: `eliminar_repuesto_valemercaderia()`**
- ✅ NUEVA: Devuelve el stock al local origen (antes estaba comentado)
- Registra el movimiento como tipo 'eliminar'
- Devuelve la cantidad al stock correspondiente

---

## 📊 Características Disponibles

### 1. **Registro Automático**
Cada operación en vales se registra automáticamente:

```
AGREGAR repuesto → Registra movimiento 'agregar'
ELIMINAR repuesto → Registra movimiento 'eliminar' + devuelve stock
```

### 2. **Diferentes Tipos de Movimientos**
- `agregar` - Repuesto sacado del stock para vale
- `eliminar` - Repuesto removido del vale (stock devuelto)
- `devolver` - Para futuras devoluciones

### 3. **Auditoría Completa**
Cada registro incluye:
- ✓ Fecha y hora exacta
- ✓ Usuario que realizó la operación
- ✓ Repuesto específico
- ✓ Cantidad
- ✓ Origen/Local
- ✓ Número de vale/voucher
- ✓ Observaciones opcionales

---

## 📈 Reportes Disponibles

### 1. **Listado General de Movimientos**
```
URL: /movimientos/
Filtros: Tipo, Repuesto, Vale, Local, Fechas
```

### 2. **Historial de Repuesto**
```
URL: /movimientos/repuesto/{id}
Muestra: Balance por período, movimientos detallados
```

### 3. **Historial de Vale**
```
URL: /movimientos/vale/{id}
Muestra: Todos los repuestos movidos en ese vale
```

### 4. **Repuestos Más Sacados**
```
URL: /movimientos/reportes/repuestos-sacados
Muestra: Top N repuestos más utilizados en vales
```

### 5. **Movimientos por Local**
```
URL: /movimientos/reportes/por-local
Muestra: Resumen de salidas/entradas por bodega
```

### 6. **Dashboard**
```
URL: /movimientos/dashboard
Muestra: Resumen general, estadísticas, gráficos
```

### 7. **Exportar a CSV**
```
URL: /movimientos/exportar?fecha_inicio=...&fecha_fin=...
Descarga: Archivo CSV con todos los movimientos
```

---

## 🔍 Consultas Útiles en PHP

### Obtener movimientos de un repuesto
```php
$movimientos = \App\vale_mercaderia_movimiento::where('repuesto_id', 5)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Historial con balance
```php
$balance = \App\Helpers\MovimientoValeHelper::balanceRepuesto(5, '2026-01-01', '2026-12-31');

echo "Entradas: " . $balance['total_entradas']; // unidades que entraron
echo "Salidas: " . $balance['total_salidas'];   // unidades que salieron
echo "Balance: " . $balance['balance_neto'];    // diferencia
```

### Repuestos más sacados
```php
$top = \App\Helpers\MovimientoValeHelper::repuestosMasSacados('2026-01-01', '2026-12-31', 10);
```

---

## ⚠️ Importante - Correción de Bug

**Problema anterior:** Cuando se eliminaba un repuesto de un vale, el stock NO se devolvía.

**Solución aplicada:** La función `eliminar_repuesto_valemercaderia()` ahora:
1. Obtiene la cantidad y local del repuesto
2. **SUMA** la cantidad al stock correspondiente (deshace la resta)
3. Guarda los cambios en el repuesto
4. Registra el movimiento
5. Elimina el registro del vale

---

## 🧪 Pruebas Recomendadas

### Test 1: Agregar un repuesto a un vale
```
1. Ir a crear/editar un vale
2. Agregar un repuesto
3. Verificar que se registra en tabla vale_mercaderia_movimientos
4. Verificar que el stock disminuyó
```

### Test 2: Eliminar un repuesto del vale
```
1. Eliminar el repuesto del vale
2. Verificar que el stock se devolvió ✓ (NUEVA FUNCIONALIDAD)
3. Verificar que se registra movimiento 'eliminar'
```

### Test 3: Ver reportes
```
1. Ir a /movimientos/
2. Filtrar por diferentes criterios
3. Verificar que muestra todos los movimientos registrados
```

---

## 📝 Estructura de Datos Registrados

Ejemplo de registro en `vale_mercaderia_movimientos`:

```
id: 1
vale_mercaderia_id: 5
repuesto_id: 42
usuario_id: 3
local_id: 1 (Bodega Principal)
cantidad: 10
tipo_movimiento: 'agregar'
numero_voucher: '2026-00123'
observaciones: 'Repuesto agregado al vale de mercadería'
created_at: 2026-06-03 14:30:22
updated_at: 2026-06-03 14:30:22
```

---

## 🔗 Relaciones de Base de Datos

```
vale_mercaderia_movimientos
├─ vale_mercaderia (FK: vale_mercaderia_id)
├─ repuestos (FK: repuesto_id)
└─ users (FK: usuario_id)
```

---

## 💡 Próximas Mejoras Sugeridas

1. **Notificaciones:** Alertas cuando stock es bajo
2. **Gráficos:** Visualizar tendencias de movimientos
3. **Estadísticas avanzadas:** Análisis de consumo por período
4. **Alertas:** Notificar repuestos faltantes
5. **Integración:** Sincronizar con compras/ventas
6. **Autorización:** Permisos para ver/editar movimientos

---

## 🆘 Troubleshooting

### Error: "Base table or view not found"
**Solución:** Ejecuta `php artisan migrate`

### Error: "Class not found"
**Solución:** Ejecuta `composer dump-autoload`

### No se registran movimientos
**Solución:** 
1. Verificar que ventas_controlador.php tiene los imports
2. Verificar que la clase helper existe en `app/Helpers/`
3. Revisar logs: `storage/logs/laravel.log`

### Stock no se devuelve al eliminar
**Solución:** Verificar que estás usando la nueva versión de `eliminar_repuesto_valemercaderia()`

---

## 📞 Soporte

Para consultas sobre:
- **Reportes:** Ver `README_MOVIMIENTOS_VALES.md`
- **Rutas:** Ver `RUTAS_MOVIMIENTOS_VALES.php`
- **Modelo:** Ver `app/vale_mercaderia_movimiento.php`
- **Helper:** Ver `app/Helpers/MovimientoValeHelper.php`

---

**Implementación completada ✅**
