// Rutas para reportes de movimientos de vales de mercadería
// Agregar estas líneas a routes/web.php

Route::middleware(['auth'])->group(function () {
    
    // Reportes de movimientos de vales
    Route::prefix('movimientos')->group(function () {
        
        // Listado principal de movimientos
        Route::get('/', 'MovimientoValeController@index')->name('movimientos.index');
        
        // Historial de un repuesto
        Route::get('/repuesto/{repuesto_id}', 'MovimientoValeController@historicoRepuesto')->name('movimientos.historico_repuesto');
        
        // Historial de un vale
        Route::get('/vale/{vale_id}', 'MovimientoValeController@historicoVale')->name('movimientos.historico_vale');
        
        // Reporte: Repuestos más sacados
        Route::get('/reportes/repuestos-sacados', 'MovimientoValeController@repuestosMasSacados')->name('movimientos.repuestos_sacados');
        
        // Reporte: Movimientos por local
        Route::get('/reportes/por-local', 'MovimientoValeController@movimientosPorLocal')->name('movimientos.por_local');
        
        // Dashboard
        Route::get('/dashboard', 'MovimientoValeController@dashboard')->name('movimientos.dashboard');
        
        // Exportar
        Route::get('/exportar', 'MovimientoValeController@exportar')->name('movimientos.exportar');
    });
});

// Si prefieres que sean AJAX, también puedes usar POST:
Route::middleware(['auth'])->group(function () {
    Route::post('/api/movimientos/buscar', 'MovimientoValeController@index');
});
