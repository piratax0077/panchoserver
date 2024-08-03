<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user_web_controlador;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/ruta_prueba','api_controlador@prueba');
Route::get('/familias','api_controlador@damefamilias');
Route::get('/dame_repuesto/{id}','api_controlador@dame_un_repuesto');

Route::get('{id}/siguiente','api_controlador@damesiguiente');
Route::get('{id}/anterior','api_controlador@dameanterior');
Route::get('{id}/damemodelos','api_controlador@damemodelos');
Route::get('{id}/dameaniosvehiculo','api_controlador@dameaniosvehiculo');
Route::get('/buscar_modelo/{idmodelo}/{idfamilia}','api_controlador@buscar_por_modelo');



Route::get('/dameofertas','api_controlador@dame_ofertas');



Route::get('/',[user_web_controlador::class, 'index']);

Route::get('/desactivar_carrito_virtual/{numero_carrito}','api_controlador@desactivar_carrito_virtual');

Route::post('/forgot-password','api_controlador@sendResetLinkEmail');

Route::get('{id}/ordenar','api_controlador@ordenar');
Route::get('{id}/ordenar_con_familia/{familia}','api_controlador@ordenar_con_familia');
Route::get('{marca}/ordenar_marca','api_controlador@ordenar_marca');
Route::get('{familia}/ordenar_familia','api_controlador@ordenar_familia');
Route::get('{id}/ordenar_modelo/{idmodelo}','api_controlador@ordenar_modelo');
Route::get('{id}/ordenar_modelo_con_familia/{idmodelo}/{familia}','api_controlador@ordenar_modelo_con_familia');
Route::get('{marca}/ordenar_modelo_marca/{idmodelo}','api_controlador@ordenar_modelo_marca');
Route::get('{familia}/ordenar_modelo_familia/{idmodelo}','api_controlador@ordenar_modelo_familia');
Route::get('{tag}/ordenar_buscador_marca/{marca}','api_controlador@ordenar_buscador_marca');

Route::get('{id}/ordenar_medida/{medida}','api_controlador@ordenar_medida');
Route::get('{id}/ordenar_medida_con_familia/{medida}/{familia}','api_controlador@ordenar_medida_con_familia');
Route::get('{id}/ordenar_oem/{oem}','api_controlador@ordenar_oem');

Route::get('{id}/ordenar_buscador/{tag}','api_controlador@ordenar_buscador');
Route::get('{id}/ordenar_buscador_con_marca/{tag}/{marca}','api_controlador@ordenar_buscador_con_marca');
Route::get('{numero_carrito}/detalle_venta','api_controlador@detalle_venta');
Route::get('/confirmar_pago/{id}','api_controlador@confirmar_pago');

Route::get('/dame_detalle_retiro/{numero_carrito}','api_controlador@dame_detalle_retiro');
Route::get('/ordenar_rango_precio/{min}/{max}','api_controlador@ordenar_rango_precio');
Route::get('/ordenar_rango_precio_con_familia/{min}/{max}/{familia}','api_controlador@ordenar_rango_precio_con_familia');
Route::get('/ordenar_tag_rango_precio/{min}/{max}/{tag}','api_controlador@ordenar_tag_rango_precio');
Route::get('/ordenar_modelo_rango_precio/{min}/{max}/{idmodelo}','api_controlador@ordenar_modelo_rango_precio');
Route::get('/ordenar_modelo_rango_precio_con_familia/{min}/{max}/{idmodelo}/{familia}','api_controlador@ordenar_modelo_rango_precio_con_familia');
Route::get('/ordenar_rango_precio_oem/{min}/{max}/{oem}','api_controlador@ordenar_rango_precio_oem');
Route::get('/ordenar_medida_rango_precio/{min}/{max}/{medida}','api_controlador@ordenar_medida_rango_precio');
Route::get('/ordenar_medida_rango_precio_con_familia/{min}/{max}/{medida}/{familia}','api_controlador@ordenar_medida_rango_precio_con_familia');
Route::get('/ordenar_marca_oem/{marca}/{oem}','api_controlador@ordenar_marca_oem');
Route::get('/ordenar_busquedamodelo_rango_precio/{min}/{max}/{idmodelo}/{idfamilia}','api_controlador@ordenar_busquedamodelo_rango_precio');
Route::get('/ordenar_marca_medida/{marca}/{medida}','api_controlador@ordenar_marca_medida');
Route::get('/ordenar_familia_medida/{familia}/{medida}','api_controlador@ordenar_familia_medida');
Route::get('/ordenar_busqueda_modelo_marca/{marca}/{idmodelo}/{idfamilia}','api_controlador@ordenar_busqueda_modelo_marca');
Route::get('/ordenar_buscador_modelo/{idfamilia}/{idmodelo}/{value}','api_controlador@ordenar_buscador_modelo');


Route::post('register', 'api_controlador@register');
Route::post('update_user', 'api_controlador@update_user');
Route::post('login', 'api_controlador@authenticate');
//Enviar correos

Route::post('/enviar_correo','api_controlador@enviar_correo');
Route::post('/restablecer_password','api_controlador@restablecer_password');

// Rutas para el demo
Route::get('{tag}/buscadordemo','api_controlador@buscador_completo_demo');
Route::get('{id}/repuestodemo','api_controlador@damerepuesto_demo');
Route::get('/dameproveedoresdemo','api_controlador@dameproveedores_demo');
Route::get('/damepormarca_demo/{id}','api_controlador@damepormarca_demo');
Route::get('/dameroles_demo','api_controlador@dameroles_demo');

Route::group(['middleware' => ['jwt.verify']], function() {
    //Todo lo que este dentro de este grupo requiere verificación de usuario.
    Route::post('get-user', 'api_controlador@getAuthenticatedUser');
    Route::get('ruta_prueba_get','api_controlador@prueba');
    Route::get('/revisar_carrito','api_controlador@revisar_carrito_virtual');
    Route::post('/agregar_carrito_virtual','api_controlador@agregar_carrito_virtual');
    Route::get('/eliminar_item_carrito/{idrep}/{numero_carrito}','api_controlador@eliminar_item_carrito');
    
    Route::post('/retiro_tienda_guardar','api_controlador@retiro_tienda_guardar');
    Route::post('/despacho_domicilio_guardar','api_controlador@despacho_domicilio_guardar');

    Route::post('/guardar_cliente','api_controlador@guardar_cliente_web');

    Route::post('/iniciar_pago','api_controlador@iniciar_pago');
    Route::post('/enviarsii_online','api_controlador@enviar_sii_online');
    Route::get('dame_solicitudes_compra','api_controlador@dame_solicitudes_compra');

    Route::get('/repuestos','api_controlador@damerepuestos');
    Route::get('/damemodelos_por_marca/{idmarca}','api_controlador@damemodelos_pormarca');
    Route::get('/damemodelo_por_marca/{idmodelo}','api_controlador@damemodelo_pormarca');
    Route::get('{tag}/buscador','api_controlador@buscador_completo');
    Route::get('/buscar_por_medida/{medida}','api_controlador@buscar_por_medida');
    Route::get('/buscar_por_oem/{oem}','api_controlador@buscar_por_oem');
    Route::get('{id}/repuesto','api_controlador@damerepuesto');
    Route::get('{familia}/buscar_medida_familia/{medida}','api_controlador@buscar_medida_familia');
    Route::get('/familias_medidas','api_controlador@familias_medidas');

    Route::post('/iniciar_pago_getnet','getnet_controlador@index');
});

