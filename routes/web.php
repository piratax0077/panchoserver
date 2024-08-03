<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\repuesto_codigo_controlador;
use App\Http\Controllers\repuestocontrolador;
use App\Http\Controllers\imprimir_controlador;
use App\Http\Controllers\user_web_controlador;
/*

|--------------------------------------------------------------------------

| Web Routes

|--------------------------------------------------------------------------

|

| Here is where you can register web routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| contains the "web" middleware group. Now create something great!

|

*/







Route::get('/intranet', function () {

    //return view('login');

});





Route::get('/layout',function(){

    return view('layoutNuevo');

});



Route::get('/pruebaNueva', function(){

    return view('pruebaNueva');

});



//Prueba de columnas

Route::get('3columnas', function()

{

	return view('errors.3columnas');

}

);



Route::get('combito', function () {

    return view('combito');

});



//Prueba de cargar DIV con ajax jquery

Route::get('cargardiv','cargadiv_controlador@ver');

Route::get('cargardiv/ver','cargadiv_controlador@verr');

Route::post('cargardiv/cargar','cargadiv_controlador@cargar');







Route::post('3columnas/guardardatos', 'tres_col@guardardatos');

Route::post('3columnas/guardarsimilares', 'tres_col@guardarsimilares');

Route::post('3columnas/guardarfotos', 'tres_col@guardarfotos');



//Route::resource('login','logincontrolador'); //RESTFULL

Route::get('loginvs','logincontrolador@login');

Route::post('loginvs','logincontrolador@loginvs');

Route::get('/avatar-user/{filename}','logincontrolador@getAvatar');



Route::middleware(['auth'])->prefix('marcavehiculo')->group(function (){

    Route::resource('/','marcavehiculocontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','marcavehiculocontrolador@destroy');

});



//MODELO VEHICULO

Route::middleware(['auth'])->prefix('modelovehiculo')->group(function ()

{

    Route::resource('/','modelovehiculocontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','modelovehiculocontrolador@destroy');

    Route::get('/{id}/ver','modelovehiculocontrolador@vermodelos');

    Route::get('/damepormarca/{id}','modelovehiculocontrolador@dame_modelos');

    Route::post('/guardar','modelovehiculocontrolador@store');

    Route::get('/dameuno/{id}','modelovehiculocontrolador@dame_un_modelo');

});



//ROL

Route::middleware(['auth'])->prefix('rol')->group(function ()

{

    Route::resource('/','rolcontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','rolcontrolador@destroy');

    Route::post('/guardar', 'rolcontrolador@store');

});



//FORMA DE PAGO

Route::middleware(['auth'])->prefix('formapago')->group(function ()

{

    Route::resource('/','formapagocontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','formapagocontrolador@destroy');

});



Route::middleware(['auth'])->prefix('familia')->group(function ()

{

    Route::resource('/','familiacontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','familiacontrolador@destroy');

});

Route::get('familiasJSON','familiacontrolador@dame_familias');
Route::get('familiascondescuento','familiacontrolador@dame_familias_con_descuento');


//PROVEEDOR

Route::middleware(['auth'])->prefix('proveedor')->group(function ()

{

    Route::resource('/','proveedorcontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','proveedorcontrolador@destroy');

    Route::get('/dametransportistas','proveedorcontrolador@dame_transportistas');

    Route::get('/dameproveedores','proveedorcontrolador@dame_proveedores');

    Route::get('/dameproveedores_array','proveedorcontrolador@dame_proveedores_array');

    Route::get('{idrepuesto}/guardarproveedor/{idproveedor}','proveedorcontrolador@guardar_proveedor');

    Route::get('/dame_stock_minimo_proveedor/{idproveedor}','proveedorcontrolador@dame_stock_minimo_proveedor');

    Route::get('/dame_stock_minimo_familia/{idfamilia}','proveedorcontrolador@dame_stock_minimo_familia');

    Route::get('/dame_stock_minimo_estado/{estado}/{mes}/{anio}','proveedorcontrolador@dame_stock_minimo_estado');

});



//MARCA REPUESTOS

Route::middleware(['auth'])->prefix('marcarepuesto')->group(function ()

{

    Route::resource('/','marcarepuestocontrolador'); //RESTFULL

    Route::get('/{id}/eliminar','marcarepuestocontrolador@destroy');

    Route::get('/{id}/destruir','marcarepuestocontrolador@destruir');

});

Route::get('marcarepuestoJSON','marcarepuestocontrolador@dame_marca_repuestos');



//PAIS

Route::middleware(['auth'])->prefix('pais')->group(function ()

{

    Route::resource('/','pais_controlador'); //RESTFULL

    Route::get('/{id}/eliminar','pais_controlador@destroy');

});



//MEDIDAS

Route::middleware(['auth'])->prefix('medidas')->group(function ()

{

    Route::resource('/','familiaMedidas_controlador'); //RESTFULL

    Route::get('/{id}/eliminar','familiaMedidas_controlador@destroy');

    Route::get('/{id}/editar','familiaMedidas_controlador@edit');

});



Route::get('paisJSON','pais_controlador@dame_paises');

Route::get('medidasJSON/{id}','familiaMedidas_controlador@dame_medidas');



//Para repuestos debemos crear propias rutas y métodos ya que utiliza 3 blades

//para guardar el  repuesto (datos del repuesto, similares, fotos)



//En el Form1 recibe datos y los manda al Form2 que recibe los similares,

//entonces datos y similares se envian a Form3 que recibe las fotos y guarda todo.

//Route::resource('repuesto','repuestocontrolador'); //RESTFULL



Route::middleware(['auth'])->prefix('repuesto')->group(function ()
{
    Route::get('/','repuestocontrolador@index');
    Route::get('/xpress','repuestocontrolador@xpress');
    Route::get('/inactivos','repuestocontrolador@inactivos');
    Route::get('/activarRepuesto/{codigo_interno}','repuestocontrolador@activarRepuesto');
    Route::get('/buscarRepuestoExpress/{dato}','repuestocontrolador@buscarRepuestoExpress');
    Route::get('/buscarRepuestoInactivos/{dato}','repuestocontrolador@buscarRepuestoInactivos'); //Eliminar
    Route::get('{id}/eliminar','repuestocontrolador@destroy'); //Eliminar
    Route::get('/eliminar/{id}','repuestocontrolador@eliminar_repuesto'); //Eliminar desde la vista Modificar_repuesto
    Route::get('/modificar','repuestocontrolador@edit');
    Route::get('/modificar/{id_repuesto}','repuestocontrolador@editar');
    Route::get('create','repuestocontrolador@create'); //ingresar datos
    Route::get('buscarcodigo/{codigo}','repuestocontrolador@buscar_por_codigo');
    Route::post('datos','repuestocontrolador@datos'); //guardar datos
    Route::post('modificado','repuestocontrolador@guardar_repuesto_modificado');
    Route::post('buscarepuestos','repuestocontrolador@buscarepuestos'); //buscar datos
    Route::get('proveedor/{id_prov}','repuestocontrolador@dame_repuestos_x_proveedor');
    Route::post('actualizasaldos','repuestocontrolador@actualiza_saldos');
    Route::get('/buscarcodint/{codint}','repuestocontrolador@dame_repuesto_x_cod_int');
    Route::get('/buscarcodint_clonar_oems/{codint}','repuestocontrolador@dame_repuesto_clonar_oem');
   // Ruta de clonaciones 
    Route::get('/clonar_oems/{codigo_origen}/{codigo_destino}','repuestocontrolador@clonar_oems');
    Route::get('/clonar_aplicaciones/{codigo_origen}/{codigo_destino}','repuestocontrolador@clonar_aplicaciones');
    Route::get('/clonar_fabricantes/{codigo_origen}/{codigo_destino}','repuestocontrolador@clonar_fabricantes');
    // Fin ruta de clonaciones
    Route::get('/deshacer_similares/{id}','repuestocontrolador@deshacer_similares');
    Route::get('/deshacer_fabricantes/{id}','repuestocontrolador@deshacer_fabricantes');
    Route::get('/buscaridrep_html/{idrep}','repuestocontrolador@dame_repuesto_x_id_html');
    Route::get('/buscaridrep_html_carrito/{idrep}','repuestocontrolador@dame_repuesto_x_id_html_carrito');
    Route::get('/buscaridrep/{idrep}','repuestocontrolador@dame_repuesto_x_id');
    Route::get('/buscar','repuestocontrolador@buscar');
    Route::get('/buscar-medida','repuestocontrolador@buscar_por_medida');
    Route::get('/buscar-medida/{medida}','repuestocontrolador@damerepuestosmedida');
    Route::get('crea_fotos','repuestocontrolador@crea_fotos'); //ingresar fotos
    Route::post('fotos','repuestocontrolador@fotos'); //guardar fotos
    Route::get('crea_similares','repuestocontrolador@crea_similares'); //ingresar similares
    Route::post('similares','repuestocontrolador@similares'); //guardar similares
    Route::post('guardaOEM','repuestocontrolador@oems'); // guardar oems
    Route::get('guardaOEM','repuestocontrolador@oems'); // guardar oems
    Route::post('guardaRecAlt','repuestocontrolador@rec_alt');
    Route::post('guardaMotor','repuestocontrolador@guardaMotor');
    Route::get('actualizar_anio_similar/{dato}','repuestocontrolador@actualizar_anio_similares');
    //Para la ventana modal en repuestos.blade.php
    Route::get('{id}/damerepuesto','repuestocontrolador@dame_datos_repuesto');
    Route::get('{id}/damesimilares','repuestocontrolador@dame_similares');
    Route::get('{id}/damereguladorvoltaje','repuestocontrolador@dame_rv');
    Route::get('{id}/damesimilares_modificar','repuestocontrolador@dame_similares_modificar');
    Route::get('{id}/damefotos','repuestocontrolador@fotos_repuesto');
    Route::get('{id}/damefotos_modificar','repuestocontrolador@dame_fotos_modificar');
    Route::get('/dame_fotos_repuesto/{id}','repuestocontrolador@dame_fotos_repuesto');
    Route::get('{id}/dameoems','repuestocontrolador@dame_oems');
    Route::get('{id}/dameoems_modificar','repuestocontrolador@dame_oems_modificar');
    Route::get('{codigo_interno}/dameoems_clonar/{tipo}','repuestocontrolador@dame_oems_clonar');
    Route::get('{codigo_interno}/dame_fabricantes_clonar','repuestocontrolador@dame_fabricantes_clonar');
    Route::get('{codigo_interno}/dame_aplicaciones_clonar','repuestocontrolador@dame_aplicaciones_clonar');
    Route::get('{id}/damefabricantes','repuestocontrolador@dame_fabricantes');
    Route::get('{id}/damefabricantes_modificar','repuestocontrolador@dame_fabricantes_modificar');
    Route::get('{id}/cambiaprecio/{preciocom}/{precioven}','repuestocontrolador@cambiaprecio');
    Route::get('guardar_precio_venta/{dato}','repuestocontrolador@guardar_precio_venta');
    //Desde venta_principal.blade
    Route::post('/guardar_xpress','repuestocontrolador@guardar_xpress');
    //Para que muestre todos los repuestos en la sección de busqueda para filtrar dinamicamente
    Route::get('/dametodorepuestos','repuestocontrolador@dameTodoRepuestos');
    Route::get('/stockrepuesto',[repuestocontrolador::class,'damestock_vista']);
    Route::post('/damestockrepuesto','repuestocontrolador@damestockrepuesto');
    Route::post('/guardarstock','repuestocontrolador@guardarstock');
    Route::get('/actualizarstock/{id}','repuestocontrolador@actualizar_stock');
    Route::post('/modificarubicacion','repuestocontrolador@modificar_ubicacion');
    Route::get('/ingresados_vendidos',[repuestocontrolador::class,'ingresados_vendidos_vista']);
    Route::get('/repuestos_por_familia/{idfamilia}',[repuestocontrolador::class,'repuestos_por_familia']);
    Route::get('/busqueda_rapida/{medidas}/{idfamilia}','repuestocontrolador@busqueda_rapida');
    Route::get('/damerv/{idrep}','repuestocontrolador@damerv');
    Route::get('/repuestos_sin_ubicacion',[repuestocontrolador::class,'repuestos_sin_ubicacion']);
    Route::get('/avanzarNumTraspaso',[repuestocontrolador::class,'avanzarNumTraspaso']);
    Route::get('/dameultimosrepuestos','repuestocontrolador@dameultimosrepuestos');
    Route::get('/resetear_tiempo_precio/{idrep}','repuestocontrolador@resetear_tiempo_precio');
    Route::get('/actualizados','repuestocontrolador@actualizados')->name('repuestos.actualizados');
    Route::get('/descargar-repuestos', 'repuestocontrolador@descargarExcel')->name('repuestos.descargar');
    Route::get('/descargar-repuestos-familia/{id}', 'repuestocontrolador@descargarRepuestosFamiliaExcel');
    Route::get('/guardar_nuevo_stock_minimo/{id}/{nuevo_stock_minimo}/{estado}', 'repuestocontrolador@guardar_nuevo_stock_minimo');
    /*Rutas para clonaciones oems, aplicaciones y fabricantes */
    Route::get('/listar-oems','repuestocontrolador@listar_oems');
    Route::get('/clonaciones','repuestocontrolador@clonaciones');
    /* FIN RUTAS CLONACIONES */
    Route::get('/deshacer_clonacion_oems/{id}','repuestocontrolador@deshacer_clonacion_oems');
    Route::get('/historial_clonaciones_oems','repuestocontrolador@dame_historial_clonaciones_oems');
    Route::get('/revisar_stock_minimo','repuestocontrolador@revisar_stock_minimo');
    Route::get('/revisar_stock_proveedor','repuestocontrolador@revisar_stock_proveedor');
    Route::get('/dame_stock_minimo_fecha','repuestocontrolador@dame_stock_minimo_fecha');
    Route::get('/damemp/{id}','repuestocontrolador@dameMotorPartida');
    Route::get('/borrarmotor/{id}','repuestocontrolador@borrarmotor');
    Route::get('/detalle_pedido/{idrep}','repuestocontrolador@detalle_pedido');
    Route::get('/guardar_detalle_pedido/{idrep}/{idproveedor}/{cantidad}/{cod_rep_prov}','repuestocontrolador@guardar_detalle_pedido');
});

Route::middleware(['auth'])->prefix('similar')->group(function ()
{
    Route::get('/{id}/eliminar','similarcontrolador@eliminar'); // id es del repuesto
    Route::get('/{id}/modificar','similarcontrolador@modificar'); // id es del repuesto
});

//COMPRAS

Route::middleware(['auth'])->prefix('compras')->group(function ()
{
    Route::get('/crear','compras_controlador@crear');
    Route::post('/guardarcabecera','compras_controlador@guardarcabecera');
    Route::post('/buscarepuestos','compras_controlador@buscarepuestos');
    Route::get('/buscarepuestosprov/{cod}','compras_controlador@buscarepuestosprov');
    Route::post('/guardaritem','compras_controlador@guardaritem');
    Route::post('/moveritem','compras_controlador@moveritem');
    Route::get('/{id}/dameultimoitem','compras_controlador@dameultimoitem');
    Route::get('/{id}/dameitems','compras_controlador@dameitemsfactura');
    Route::get('/{id}/eliminar','compras_controlador@eliminaritem');
    Route::get('/listar','compras_controlador@listar');
    Route::get('/listar_por_factura','compras_controlador@listar_por_factura');
    Route::get('/listar_factura_numero/{num_factura}','compras_controlador@listar_factura_numero');
    Route::get('/{id}/proveedor','compras_controlador@dame_facturas_por_proveedor');
    Route::get('/{id}/proveedorjson','compras_controlador@dame_facturas_por_proveedor_json');
    Route::get('/{id}/damefactura','compras_controlador@dame_factura');
    Route::post('/pagar_factura','compras_controlador@pagar_factura');
    Route::get('/dame_item_factura/{id_repuesto}/{id_factura}','compras_controlador@dame_item_factura');
    Route::post('/actualizar_item_factura','compras_controlador@actualizar_item_factura');
    Route::post('/eliminar_repuesto_factura','compras_controlador@eliminar_repuesto_factura');
    Route::get('/{id}/damefacturacab','compras_controlador@damefacturacab');
});



//FACTU PRODU

Route::middleware(['auth'])->prefix('factuprodu')->group(function ()
{
    Route::get('/crear','factuprodu_controlador@crear');
    Route::get('/{id}/utilidad','factuprodu_controlador@dameporcentaje');
    Route::get('/{id}/medidas','factuprodu_controlador@damemedidas');
    Route::post('/guardaritem','factuprodu_controlador@guardaritem');
    Route::post('/guardarfoto','factuprodu_controlador@guardarfoto');
    Route::post('/guardarsimilar','factuprodu_controlador@guardarsimilar');
    Route::post('/guardaroem','factuprodu_controlador@guardaroem');
    Route::get('/{cod}/buscarepuesto/{idprov}','factuprodu_controlador@buscarepuesto');
    Route::get('/verificafactura/{num}','factuprodu_controlador@verifica_factura');
    Route::get('/{idfoto}/borrarfoto/{idrep}','factuprodu_controlador@borrar_foto');
    Route::get('/{idsimilar}/borrarsimilar/{idrep}','factuprodu_controlador@borrar_similar');
    Route::get('/{idoem}/borraroem/{idrep}','factuprodu_controlador@borrar_oem');
    Route::get('/{idoem}/borraroem_x_codint/{codigo_interno}/{tipo}','factuprodu_controlador@borrar_oem_x_codint');
    Route::get('/{crp}/proveedor/{idprov}/factura/{idfac}','factuprodu_controlador@verificacodprov');
    Route::post('/guardarfab','factuprodu_controlador@guardarfab');
    Route::get('/{idfab}/borrarfab/{idrep}','factuprodu_controlador@borrar_fab');
    Route::get('/damecompras/{idrep}','factuprodu_controlador@dame_compras');
    Route::get('/damefactura/{num}','factuprodu_controlador@dame_factura');
    Route::post('/import-excel', 'factuprodu_controlador@import');
    Route::get('/{idrv}/borrarrv/{idrep}','factuprodu_controlador@borrarrv');
    Route::post('/eliminar_medida_familia','factuprodu_controlador@eliminar_medida_familia');
    Route::post('/guardar_edicion_medida','factuprodu_controlador@guardar_edicion_medida');
});



//LIMITE DE CRÉDITO

Route::middleware(['auth'])->prefix('limitecredito')->group(function ()

{

    Route::get('/','limite_controlador@index');

    Route::post('/guardar','limite_controlador@store');

    Route::get('/dame','limite_controlador@damelimites');

    Route::get('/{id}/borrar','limite_controlador@destroy');

});



//DÍAS DE CRÉDITO

Route::middleware(['auth'])->prefix('diascredito')->group(function ()

{

    Route::get('/','dias_controlador@index');

    Route::post('/guardar','dias_controlador@store');

    Route::get('/dame','dias_controlador@damedias');

    Route::get('/{id}/borrar','dias_controlador@destroy');

});



//CLIENTES

Route::middleware(['auth'])->prefix('clientes')->group(function ()
{
    Route::get('/','clientes_controlador@index');
    Route::get('/estado','clientes_controlador@estadocliente');
    Route::post('/guardar','clientes_controlador@store');
    Route::get('/xpress','clientes_controlador@cliente_xpress_abrir');
    Route::get('/xpress_listar_todos','clientes_controlador@cliente_xpress_listar_todos');
    Route::get('/xpress_actualizar_estado_envio/{dato}','clientes_controlador@cliente_xpress_actualizar_estado_envio');
    Route::post('/guardar_cliente_xpress','clientes_controlador@cliente_xpress_guardar');
    Route::post('/guardar_cliente_xpress_nc','clientes_controlador@guardar_cliente_xpress_nc');
    Route::post('/agregarfamilia','clientes_controlador@agregar_familia');
    Route::post('/buscar','clientes_controlador@buscar');
    Route::get('/{id}/cargar','clientes_controlador@cargar');
    Route::get('/{id}/borrar','clientes_controlador@destroy');
    Route::get('/{id}/borrardeuda','clientes_controlador@borrar_deuda_cero');
    //Agrega descuento por familia en clientes
    Route::post('/descfam','clientes_controlador@descfam');
    Route::get('/{id}/borrarfam','clientes_controlador@borrarfam');
    Route::get('/borrarfamtodo','clientes_controlador@borrarfamtodo');
    Route::get('/{id}/cargardescuentos','clientes_controlador@damedescuentos');
    Route::get('/dame_cuenta/{id}','clientes_controlador@damecuenta');
    Route::post('/agrega_cuenta','clientes_controlador@agregacuenta');
    Route::get('/borrar_cuenta/{data}','clientes_controlador@borrarcuenta');
    Route::get('/agregar_documento/{data}','clientes_controlador@agregar_documento');
    Route::get('/dame_tipo_documentos/{idc}','clientes_controlador@dame_documentos_cliente');
    Route::get('/borrar_documento_cliente/{data}','clientes_controlador@borrar_documento_cliente');
    Route::post('/abonar','clientes_controlador@abonar');
    Route::post('/abonar_detalle','clientes_controlador@abonar_detalle');
    Route::get('/dame_abonos','clientes_controlador@dame_abonos');
    Route::get('/dame_vales_mercaderia','clientes_controlador@dame_vales_mercaderia');
    Route::get('/dame_vales_consignacion','clientes_controlador@dame_vales_consignacion');
    Route::get('/morosos','clientes_controlador@clientes_morosos');
    Route::post('/agregar_giro','clientes_controlador@agregar_giro');
    Route::get('/dame_giros/{id_cliente}','clientes_controlador@dame_giros');
});

Route::get('clientes_buscar/{id}','clientes_controlador@cuenta_busqueda_clientes');

//VENTAS

Route::middleware(['auth'])->prefix('ventas')->group(function ()
{
    Route::get('/','ventas_controlador@index');
    Route::get('/damemarcas','ventas_controlador@damemarcas');
    Route::get('/damemodelos/{idmarca}','ventas_controlador@damemodelos');
    Route::get('/damefamilias/{idmodelo}','ventas_controlador@dame_familias_repuestos');
    Route::get('/{idfamilia}/{dato}/damerepuestos','ventas_controlador@dame_repuestos');
    Route::post('/agregar_carrito','ventas_controlador@agregar_carrito');
    Route::post('/agregar_carrito_transferido','ventas_controlador@agregar_carrito_transferido');
    Route::get('/dame_carrito','ventas_controlador@dame_carrito_vista');
    Route::get('/recargar_carrito','ventas_controlador@recargar_carrito_vista');
    Route::get('/recargar_carrito_transferido/{id}','ventas_controlador@recargar_carrito_transferido_vista');
    Route::get('/dame_carrito_transferido','ventas_controlador@dame_carrito_transferido_vista');
    Route::get('/dame_carrito_transferido_cliente/{id}','ventas_controlador@dame_carrito_transferido_vista_cliente');
    Route::get('/borrar_carrito/{cual}','ventas_controlador@borrar_carrito');
    Route::get('/borrar_carrito_cliente/{cliente_id}','ventas_controlador@borrar_carrito_cliente');
    Route::get('/{item}/borrar_item_carrito','ventas_controlador@borrar_item_carrito');
    Route::get('/{item}/borrar_item_carrito_transferido','ventas_controlador@borrar_item_carrito_transferido');
    Route::get('/descuento_carrito/{id_cliente}','ventas_controlador@descuentos_carrito');
    Route::get('/descuento_carrito_transferido/{id_cliente}/{numero_carro}','ventas_controlador@descuentos_carrito_transferido');
    Route::get('/dame_forma_pago','ventas_controlador@dame_formas_pago');
    Route::get('/dame_forma_pago_delivery','ventas_controlador@dame_formas_pago_delivery');
    Route::get('/dame_forma_pago_modificar_pagos','ventas_controlador@dame_formas_pago_modificar_pagos');
    Route::post('/generarxml','ventas_controlador@generar_xml');
    Route::post('/enviarsii','ventas_controlador@enviar_sii');
    Route::get('/verestado/{trackid}','ventas_controlador@revisar_mail_estado');
    Route::post('/agregar_pago','ventas_controlador@agregar_pago');
    Route::post('/actualizar_pago','ventas_controlador@actualizar_pago');
    Route::get('/cargar_pago/{id_pago}','ventas_controlador@cargar_pago');
    Route::post('/guardarventa','ventas_controlador@guardar_venta');
    Route::post('/cotizar','ventas_controlador@cotizar');
    Route::post('/consignar','ventas_controlador@consignar');
    Route::get('/imprimir_cotizacion/{num}','imprimir_controlador@imprimir_cotizacion');
    Route::get('/imprimir_consignacion/{num_consignacion}','imprimir_controlador@imprimir_consignacion');
    Route::post('/imprimir_arqueo','imprimir_controlador@imprimir_arqueo');
    Route::get('/ofertas','ventas_controlador@ofertas');
    Route::post('/damerepuesto_oferta','ventas_controlador@damerepuesto_oferta');
    Route::post('/damerepuesto_kit','ventas_controlador@damerepuesto_kit');
    Route::post('/aplicar_descuento','ventas_controlador@aplicar_descuento');
    Route::get('/historial_cotizaciones','ventas_controlador@dame_historial_cotizaciones_vista');
    Route::get('/eliminar_cotizacion/{id}','ventas_controlador@eliminar_cotizacion');
    Route::get('/damehistorialcotizaciones/{id_cliente}','ventas_controlador@dame_historial_cotizaciones');
    Route::get('/buscar_cotizacion/{tag}','ventas_controlador@buscar_cotizacion');

    Route::get('/eliminar_factura/{id_factura}','ventas_controlador@eliminar_factura');

    Route::get('/dameofertas','api_controlador@dame_ofertas');
    Route::get('/all_ofertas','ventas_controlador@all_ofertas');
    Route::get('/dameoferta/{id}','ventas_controlador@dameoferta');
    Route::post('/confirmar_edicion_oferta','ventas_controlador@confirmar_edicion_oferta');

    Route::post('/imprimir_vale','imprimir_controlador@imprimir_vale');
    Route::post('/imprimir_vale_resultado','imprimir_controlador@imprimir_vale_resultado');

    //Route::get('/{id}','ventas_controlador@dame_relacionados');
    Route::get('/buscardescripcion/{descripcion}','ventas_controlador@buscar_por_descripcion');
    Route::get('/buscarcodproveedor/{codigo}','ventas_controlador@buscar_por_codigo_proveedor');
    Route::get('/buscaroem/{oem}','ventas_controlador@buscar_por_oem');
    Route::get('/buscarcodfabricante/{codfab}','ventas_controlador@buscar_por_codigo_fabricante');
    Route::get('/buscarmedidas/{medida}','ventas_controlador@buscar_por_medidas');
    Route::get('/buscarcodint/{codint}','ventas_controlador@buscar_por_codigo_interno');
    Route::get('/buscarmodelo/{modelo}','ventas_controlador@buscar_por_modelo');
    Route::get('/verificarnombrecarrito/{nombre}/{idcliente}','ventas_controlador@verificar_nombre_carrito');
    Route::get('/guardarcarritocompleto/{nombre}/{existe}','ventas_controlador@guardar_carrito_completo');
    Route::get('/cargarcarritocompleto/{quien}','ventas_controlador@cargar_carrito_completo');
    Route::get('/cargarcarritocompleto_transferido/{nombrecarrito}/{vendedor_id}','ventas_controlador@cargar_carrito_completo_transferido');
    Route::get('/damecarritosguardados','ventas_controlador@dame_carritos_guardados');
    Route::get('/damecotizaciones/{id_cliente}','ventas_controlador@dame_cotizaciones');
    Route::get('/damecotizacionesmes/{mes}','ventas_controlador@dame_cotizaciones_mes');
    Route::get('/cargarcotizacion/{num_cotizacion}','ventas_controlador@cargar_cotizacion');

    Route::get('/cargarcotizacion_bodega/{num_cotizacion}','ventas_controlador@cargar_cotizacion_bodega');
    Route::get('/cargarconsignacion/{num_consignacion}','ventas_controlador@cargar_consignacion');
    Route::get('/damedteporfechas/{tipodte}/{fechainicial}/{fechafinal}','ventas_controlador@damedteporfechas');
    Route::get('/dameventasporfechas/{tipo_dte}/{fechainicial}/{fechafinal}','ventas_controlador@dameventasporfechas');
    Route::get('/damedetalleboleta/{tipo_dte}/{id_boleta}','ventas_controlador@damedetalleboleta');
    Route::get('/damedetalleboleta_num_doc/{tipo_dte}/{num_doc}','ventas_controlador@damedetalleboleta_num_doc');
    Route::get('/limpiarsesion','ventas_controlador@limpiar_sesion');
    Route::get('/setxmlimprimir','ventas_controlador@set_xml_imprimir');
    Route::get('/arqueocaja','ventas_controlador@arqueo');
    Route::get('/damecajeros','ventas_controlador@dame_cajeros');
    Route::get('/transferir_carrito/{id}/{cliente_id}/{titulo}','ventas_controlador@transferir_carrito_completo');
    Route::get('/arqueo/detalle/{info}','ventas_controlador@arqueo_detalle');
    Route::get('/pedidos','ventas_controlador@pedidos');
    Route::get('/pedidos_nuevo','ventas_controlador@pedidos_nuevo');
    Route::post('/eliminaritem','ventas_controlador@eliminaritem');
    Route::get('/eliminar_pedido/{id}/{id_abono}','ventas_controlador@eliminarpedido');
    Route::get('/eliminar_abono/{id}','ventas_controlador@eliminar_abono');
    Route::get('/eliminar_abono_modal/{id}','ventas_controlador@eliminar_abono_modal');
    Route::get('/nuevo_pedido/{id}','ventas_controlador@nuevo_pedido');
    Route::post('/nuevo_abono','ventas_controlador@nuevo_abono');
    Route::post('/nueva_consignacion','ventas_controlador@nueva_consignacion');
    Route::get('/vale_por_mercaderia','ventas_controlador@vale_por_mercaderia');
    Route::get('/vale_por_mercaderia/{num_vale}','ventas_controlador@busqueda_vale_mercaderia');
    Route::post('/guardar_vale_mercaderia','ventas_controlador@guardar_vale_mercaderia');
    Route::get('/eliminar_vale_mercaderia/{id}','ventas_controlador@eliminar_vale_mercaderia');
    Route::get('/eliminar_vale_consignacion/{id}','ventas_controlador@eliminar_vale_consignacion');
    Route::get('/cargar_pedido/{id_pedido}','ventas_controlador@cargar_pedido');
    Route::get('/imprimir_pedido/{id_pedido}','imprimir_controlador@imprimir_pedido');
    Route::get('/dame_abono/{id}','ventas_controlador@dame_abono');
    Route::get('/dame_consignacion/{id}','ventas_controlador@dame_consignacion');
    Route::get('/detalle_ventas','ventas_controlador@detalle_ventas_vista');
    Route::post('/agregar_repuesto_valemercaderia','ventas_controlador@agregar_repuesto_valemercaderia');
    Route::post('/agregar_repuesto_valeconsignacion','ventas_controlador@agregar_repuesto_valeconsignacion');
    Route::get('/eliminar_repuesto_valemercaderia/{id}/{numero_vm}','ventas_controlador@eliminar_repuesto_valemercaderia');
    Route::get('/eliminar_repuesto_valeconsignacion/{numero_vc}/{id_repuesto}','ventas_controlador@eliminar_repuesto_valeconsignacion');
    Route::get('/devolver_repuesto_valeconsignacion/{numero_vc}/{id_repuesto}','ventas_controlador@devolver_repuesto_valeconsignacion');
    Route::get('/ventas_online','ventas_controlador@ventas_online');

    Route::get('/eliminar_oferta/{id}','ventas_controlador@eliminar_oferta');
    Route::get('/estadisticas','ventas_controlador@estadisticas');
    Route::get('/estadisticas_resumen/{dato}','ventas_controlador@estadisticas_resumen');
    Route::get('/armar-kit','ventas_controlador@armar_kit');
    Route::post('/agregar_repuesto_kit','ventas_controlador@agregar_repuesto_kit');
    Route::get('/crear_kit/{nombre}','ventas_controlador@crear_kit');
    Route::get('/eliminar_repuesto_kit/{idrep}/{idkit}','ventas_controlador@eliminar_repuesto_kit');
    Route::get('/seleccionar_kit/{idrep}/{local_id}','ventas_controlador@seleccionar_kit');
    Route::get('/vale_consignacion','ventas_controlador@vale_consignacion');
    Route::get('/dame_vale_consignacion/{numero_boucher}','ventas_controlador@dame_vale_consignacion');
    Route::get('/cerrar_consignacion/{id_vale}','ventas_controlador@cerrar_consignacion');
    Route::get('/detalle_vale_mercaderia/{numero_vale}','ventas_controlador@detalle_vale_mercaderia');
    Route::get('/filtrar_vales/{value}','ventas_controlador@filtrar_vales');
    Route::get('/cotizaciones','ventas_controlador@cotizaciones')->middleware('auth');
    Route::get('/cambiar_estado_cotizacion/{id}/{opcion}','ventas_controlador@cambiar_estado_cotizacion')->middleware('auth');

    Route::post('/guardar_dcto_familia','ventas_controlador@guardar_dcto_familia');
    Route::get('/eliminar_descuento_familia/{id}','ventas_controlador@eliminar_descuento_familia');
    Route::get('/revisar_pedidos','ventas_controlador@revisar_pedidos');
    Route::get('/editar_descuento_familia/{id}','ventas_controlador@modal_editar_descuento_familia');
    Route::post('/editar_nueva_familia','ventas_controlador@editar_nueva_familia');
    Route::get('/eliminar_consignacion/{id}','ventas_controlador@eliminar_consignacion');
    Route::get('/eliminar_consignacion_numero/{num_consignacion}','ventas_controlador@eliminar_consignacion_numero');
    Route::get('/modificarDte/{valor}/{num_dte}/{tipo_dte}','ventas_controlador@modificarDte');
    Route::get('/verificar_carrito_transferido','ventas_controlador@verificar_carrito_transferido');
    Route::get('/cargar_consignacion_home/{num_consignacion}','ventas_controlador@cargar_consignacion_home');
    Route::get('/renovar_abono/{id}','ventas_controlador@renovar_abono');

    Route::get('/imprimir_pedido_admin/{id_pedido}','imprimir_controlador@imprimir_pedido_admin');
    Route::get('/metas','ventas_controlador@metas');
    Route::get('/dame_porcentaje_ventas','ventas_controlador@dame_porcentaje_ventas');
});

Route::post('guardar_meta','ventas_controlador@guardar_meta')->name('guardar_meta');
Route::post('eliminar_meta','ventas_controlador@eliminar_meta')->name('eliminar_meta');
Route::post('buscar_meta','ventas_controlador@buscar_meta')->name('buscar_meta');

//NOTA DE CRÉDITO

Route::middleware(['auth'])->prefix('notacredito')->group(function ()

{

    Route::get('/','nota_credito_controlador@index');

    Route::get('/cargar_documento/{doc}','nota_credito_controlador@cargar_documento');

    Route::post('/generarxml','nota_credito_controlador@generar_xml');

    Route::post('/enviarsii','nota_credito_controlador@enviar_sii');

    Route::get('/verestado/{trackid}','nota_credito_controlador@revisar_mail_estado');

    Route::get('/verestado/{trackid}','nota_credito_controlador@revisar_mail_estado');

    Route::post('/guardar_nota','nota_credito_controlador@guardar_nota');

    Route::get('/dame_nota_credito/{num_nc}','nota_credito_controlador@dame_nota_credito');

    Route::get('/dame_nota_credito_detalle/{id_nc}','nota_credito_controlador@dame_nota_credito_detalle');



});



//NOTA DE DÉBITO

Route::middleware(['auth'])->prefix('notadebito')->group(function ()

{

    Route::get('/','nota_debito_controlador@index');

    Route::get('/cargar_documento/{doc}','nota_debito_controlador@cargar_documento');

    Route::get('/existe_nc/{nc}','nota_debito_controlador@existe_nc');

    Route::post('/guardar_nota','nota_debito_controlador@guardar_nota');

    Route::post('/generarxml','nota_debito_controlador@generar_xml');

    Route::post('/enviarsii','nota_debito_controlador@enviar_sii');

    Route::post('/actualizar_estado','nota_debito_controlador@actualizar_estado');

});



//GUIA DE DESPACHO

Route::middleware(['auth'])->prefix('guiadespacho')->group(function ()
{
    Route::get('/','guia_despacho_controlador@index');
    Route::get('/cargar_documento/{doc}','guia_despacho_controlador@cargar_documento');
    Route::get('/damecliente/{rut}','guia_despacho_controlador@dame_cliente');
    Route::get('/existe_gd/{gd}','guia_despacho_controlador@existe_gd');
    Route::get('/dame_cotizacion_num/{num}','guia_despacho_controlador@dame_cotizacion_num');
    Route::post('/procesar_guia','guia_despacho_controlador@procesar_guia');
    Route::post('/guardar_guia','guia_despacho_controlador@guardar_guia');
    Route::post('/generarxml','guia_despacho_controlador@generar_xml');
    Route::post('/enviarsii','guia_despacho_controlador@enviar_sii');
    Route::post('/actualizar_estado','guia_despacho_controlador@actualizar_estado');
    // ----------------------------------------------------------------------------------------------------------------------- //
    Route::get('/traspaso_mercaderia','guia_despacho_controlador@traspaso_mercaderia')->name('guiadespacho.traspaso_mercaderia');
    Route::get('/recepcion_mercaderia','guia_despacho_controlador@recepcion_mercaderia')->name('guiadespacho.recepcion_mercaderia');
    Route::get('/devolucion_mercaderia','guia_despacho_controlador@devolucion_mercaderia');
    Route::post('/traspasar_mercaderia','guia_despacho_controlador@traspasar_mercaderia');
    Route::get('/buscarsolicitud/{num_solicitud}','guia_despacho_controlador@buscarsolicitud');
    Route::post('/aceptar_traspaso_repuesto','guia_despacho_controlador@aceptar_traspaso_repuesto');
    Route::post('/rechazar_traspaso_repuesto','guia_despacho_controlador@rechazar_traspaso_repuesto');
    Route::get('/resumen','guia_despacho_controlador@resumen_solicitudes');
    Route::get('/detalle/{fecha}','guia_despacho_controlador@detalle');
    Route::get('/detalle_solicitud/{num_solicitud}','guia_despacho_controlador@detalle_solicitud');
    Route::get('/pendientes','guia_despacho_controlador@pendientes');
    Route::get('/detalle_nc/{dato}','guia_despacho_controlador@dame_nc');
    Route::get('/detalle_nc_nueva/{numero_nc}','guia_despacho_controlador@dame_nc_nueva');
    Route::get('/detalle_doc_nuevo/{numero_doc}/{tipo_doc}','guia_despacho_controlador@dame_doc_nuevo');
    Route::post('/devolucion','guia_despacho_controlador@devolucion');
    Route::get('/cerrar_devolucion/{num_nc}','guia_despacho_controlador@cerrar_devolucion');
    Route::get('/cerrar_devolucion_/{num_doc}/{tipo_doc}','guia_despacho_controlador@cerrar_devolucion_');
    Route::get('/dame_devoluciones/{num_nc}','guia_despacho_controlador@dame_devoluciones');
    Route::post('/opciones_devolucion','guia_despacho_controlador@opcion_devolucion');
    Route::get('/historial_devolucion/{numero_nc}','guia_despacho_controlador@historial_nc');
    
});



//ORDEN TRANSPORTE

Route::middleware(['auth'])->prefix('ot')->group(function ()

{

    Route::get('/','ot_controlador@index');

    Route::post('/guardarcabecera','ot_controlador@guardar_cabecera');

    Route::post('/guardardetalle','ot_controlador@guardar_detalle');

    Route::post('/guardardetallegrupo','ot_controlador@guardar_detalle_grupo');

    Route::get('/verificar_ot/{dato}','ot_controlador@verificar_ot');

    Route::get('/verificar_grupos/{idgrupo}','ot_controlador@verificar_grupos');

});



//SALIDA DINERO CAJA

Route::middleware(['auth'])->prefix('salida_dinero_caja')->group(function ()

{

    Route::get('/','salida_dinero_caja_controlador@index');

    Route::get('/damesalidas/{fecha}','salida_dinero_caja_controlador@dame_salidas');

    Route::post('/guardar','salida_dinero_caja_controlador@guardar');

    Route::post('/modificar','salida_dinero_caja_controlador@modificar');

    Route::get('/borrar/{id}','salida_dinero_caja_controlador@borrar');

});



Route::get('parametros','parametros_controlador@index');

Route::post('parametros/guardar','parametros_controlador@guardar');

Route::get('dameparametro/{id}','parametros_controlador@dameparametro');

Route::get('eliminarparametro/{id}','parametros_controlador@eliminarparametro');



Route::get('relacionados','relacionados_controlador@index');

//Route::post('relacionados/guardar','relacionados_controlador@guardar');

Route::get('damerelacionados/{id}','relacionados_controlador@damerelacionados');

Route::get('eliminarprincipal/{id}','relacionados_controlador@eliminarprincipal');

Route::get('eliminarrelacionado/{id}','relacionados_controlador@eliminarrelacionado');

Route::get('relacionados/{familia}/{marca}/{modelo}/damerepuestos','relacionados_controlador@dame_repuestos');

Route::get('relacionadoprincipal/{idrep}','relacionados_controlador@dame_un_repuesto');

Route::get('relacionadoguardar/{idrel}/{idpri}','relacionados_controlador@guardar_relacionado');

Route::get('/');

Route::get('bustrap431', function () {

    return view('errors.bustrap431');

});



//IMPRESIONES

Route::get('imprimir/{xml}','imprimir_controlador@imprimir')->middleware('auth');
Route::get('imprimir_pdf/{pdf}','imprimir_controlador@imprimir_pdf')->middleware('auth');
Route::get('imprimir_pdf_vale_historial/{pdf}','imprimir_controlador@imprimir_pdf_vale_mercaderia')->middleware('auth');
Route::get('imprimir_pdf_vale_consignacion_historial/{pdf}','imprimir_controlador@imprimir_pdf_vale_consignacion')->middleware('auth');

//SERVICIOS SII



Route::middleware(['auth'])->prefix('sii')->group(function ()

{

    Route::get('/', function () {

        return view('prueba_sii');

    });

    Route::get('/estadodte','sii_controlador@estadodte');

    Route::get('/detalle', 'sii_controlador@detalle');

    Route::get('/emails/{trackID}','sii_controlador@emails');

    Route::get('/verestado/{id}','sii_controlador@ver_estadoUP');

    Route::get('/verestadotrack/{id}','sii_controlador@ver_estadotrack');

    Route::get('/verestado','sii_controlador@ver_estado');

    Route::post('/guardarcaf','sii_controlador@guardar_caf');

    Route::get('/damelocales','sii_controlador@damelocales');

    Route::get('/damedocumentos/{idlocal}','sii_controlador@damedocumentos');

    Route::get('/cargarfolios','sii_controlador@cargarfolios');

    Route::get('/anularfolios','sii_controlador@anularfolios');

    Route::get('/revisarfolios/{data}','sii_controlador@revisar_folios');

    Route::get('cambiarnumeracion/{dato}','sii_controlador@cambiarnumeracion');

    Route::get('/ambiente',function(){

        return view('certificacion.ambiente');

    });

    Route::get('/basico','sii_controlador@basico');

    Route::get('/libroventas','sii_controlador@libroventas');

    Route::get('/librocompras','sii_controlador@librocompras');

    Route::get('/setguias','sii_controlador@setguias');

    Route::get('/libroguias','sii_controlador@libroguias');

    Route::get('/simulacion','sii_controlador@simulacion');

    Route::get('/intercambio','sii_controlador@intercambio');

    Route::get('/generarPDF','sii_controlador@generarPDF');

    Route::get('/basico_boletas','sii_controlador@basico_boletas');

    Route::get('/rcof_boletas','sii_controlador@rcof_boletas');

    Route::get('/pdfcito','sii_controlador@pdfcito');

    Route::get('/directo','ventas_controlador@envio_directo');

});





Route::middleware(['auth'])->prefix('rcof')->group(function ()

{

    Route::get('/','rcof_controlador@rcof_boletas');

    Route::get('/listar_rcof/{mes}','rcof_controlador@listar_rcof');

    Route::get('/crear_rcof/{mes}','rcof_controlador@crear_rcof');

    Route::get('/procesar/{fecha}','rcof_controlador@procesar');

    Route::get('/enviar_sii/{fecha}','rcof_controlador@enviar_sii');

    Route::get('/ver_estado/{fecha}','rcof_controlador@ver_estado');

    Route::get('/ver_detalle/{fecha}','rcof_controlador@ver_detalle');

    Route::get('/actualizar_estado/{info}','rcof_controlador@actualizar_estado_BD');

});



Route::middleware(['auth'])->prefix('libro')->group(function ()

{

    Route::get('/ventas','libros_controlador@libro_ventas');

    Route::get('/ventas_resumen/{data}','libros_controlador@libro_ventas_resumen');

    Route::get('/ventas_detalle/{data}','libros_controlador@libro_ventas_detalle');

    Route::get('/ventas_generar_xml/{data}','libros_controlador@libro_ventas_generar_xml');

    Route::get('/ventas_enviar_sii/{data}','libros_controlador@libro_ventas_enviar_sii');

    Route::get('/compras','libros_controlador@libro_compras');

});



Route::middleware(['auth'])->prefix('reportes')->group(function ()
{
    Route::get('/ventasdiarias','reportes_controlador@ventasdiarias');
    Route::get('/documentosgenerados','reportes_controlador@documentosgenerados');
    Route::get('/documentosgenera2','reportes_controlador@documentosgenera2');
    Route::get('/buscar_documentos/{info}','reportes_controlador@buscar_documentos');
    Route::get('/transbank','reportes_controlador@transbank');
    Route::get('/transbank_mes/{data}','reportes_controlador@transbank_mes');
    Route::get('/transbank_dia/{fecha}','reportes_controlador@transbank_dia');
    Route::get('/getnet','reportes_controlador@getnet');
    Route::get('/getnet_mes/{data}','reportes_controlador@getnet_mes');
    Route::get('/getnet_dia/{fecha}','reportes_controlador@getnet_dia');
    Route::get('/detalle_documentosgenerados/{data}','reportes_controlador@detalle_documentosgenerados');
    Route::get('/totales/{fecha}','reportes_controlador@reporte_pagos');
    Route::get('/totales_mensuales/{fecha}','reportes_controlador@reporte_pagos_mensuales');
    Route::get('/detalle/{info}','reportes_controlador@detalle');
    Route::get('/rechazados_mes/{info}','reportes_controlador@dame_rechazados_mes');
    Route::get('/deliverys_pendientes/{fecha}','reportes_controlador@delivery_pendientes_html');
    Route::get('/imprimir_detalle_dia/{fecha}','reportes_controlador@imprimir_detalle_dia');
    Route::get('/imprimir_detalle_dia_getnet/{fecha}','reportes_controlador@imprimir_detalle_dia_getnet');
    Route::get('/imprimir_repuestos_por_proveedor/{id_proveedor}','reportes_controlador@imprimir_repuestos_por_proveedor');
    Route::get('/bajo_stock_home','reportes_controlador@bajo_stock_home');
    Route::get('/bajo_stock','reportes_controlador@bajo_stock_html');
    Route::get('/rep_sin_prov','reportes_controlador@rep_sin_prov');
    Route::get('/ventas_online_detalle/{fecha}','reportes_controlador@ventas_online_detalle');
    Route::get('/ventas_online_dia/{fecha}','reportes_controlador@ventas_online_dia');
    Route::get('/detalle_carrito_virtual/{numero_carrito}','reportes_controlador@detalle_carrito_virtual');
    Route::post('/confirmar_envio','reportes_controlador@confirmar_envio');
    Route::post('/descontar_stock_carrito_virtual','reportes_controlador@descontar_stock_carrito_virtual');
    Route::get('/conexiones/{fecha}','reportes_controlador@conexiones');
    Route::get('/repuestos_actualizados','repuestocontrolador@descargarExcel');
    Route::get('/buscar_periodo_stock_minimo/{mes}/{anio}','reportes_controlador@buscar_periodo_stock_minimo');
});



//EJEMPLI-EMPEZANDO CON VUE

Route::get('/vue',function(){

    return view('bienvenida2');

});



Route::middleware(['auth'])->prefix('usuarios')->group(function ()
{
    // Route::get('/','users_controlador@index');
    Route::get('/','gestionUsuarios_controlador@index');
    // Route::get('crear','users_controlador@create');
    Route::get('/avatar/{filename}','gestionUsuarios_controlador@getAvatar')->name('user.avatar');
    Route::get('/crear','gestionUsuarios_controlador@create');
    Route::post('/save', 'gestionUsuarios_controlador@saveUser')->name('user.save');
    // Ruta para editar datos de usuario
    Route::get('/edit/{id}','gestionUsuarios_controlador@edit');
    // Ruta para guardar datos de usuario editado
    Route::post('/update','gestionUsuarios_controlador@update')->name('user.update');
    Route::get('/user/{id}','gestionUsuarios_controlador@getUser')->name('user');
    Route::get('/delete/{id}','gestionUsuarios_controlador@delete')->name('user.delete');
    Route::post('/cambiar-rol','gestionUsuarios_controlador@cambioRol')->name('user.cambioRol');
    Route::get('/up/{id}','gestionUsuarios_controlador@userUp')->name('user.up');
    Route::get('/down/{id}','gestionUsuarios_controlador@userDown')->name('user.down');
    Route::post('/permisos/add','gestionUsuarios_controlador@agregarPermisos')->name('user.agregarPermiso');
    Route::post('/permisos/delete','gestionUsuarios_controlador@quitarPermisos')->name('user.quitarPermiso');
    Route::get('/rendimiento','gestionUsuarios_controlador@rendimiento_vista');
    Route::get('/cambiar_div/{permisoId}','gestionUsuarios_controlador@cambiar_div_vista');
    Route::post('/guardarPermisosDetalles','gestionUsuarios_controlador@guardarPermisosDetalles');
    Route::get('/servidor','gestionUsuarios_controlador@usuario_servidor_vista');
    Route::get('/usuario_servidor/{id}','gestionUsuarios_controlador@usuario_servidor');
    Route::post('/agregar_usuario_servidor','gestionUsuarios_controlador@agregar_usuario_servidor');
    Route::post('/editar_usuario_servidor_post','gestionUsuarios_controlador@editar_usuario_servidor_post');
    Route::get('/editar_usuario_servidor/{usuario_id}','gestionUsuarios_controlador@editar_usuario_servidor');
    Route::get('/eliminar_usuario_servidor/{usuario_id}','gestionUsuarios_controlador@eliminar_usuario_servidor');
    Route::post('/guardarCajerosDisponibles','gestionUsuarios_controlador@guardar_cajeros_disponibles');
    Route::get('/eliminar_cajero_disponible/{usuario_id}','gestionUsuarios_controlador@eliminar_cajero_disponible');
    Route::get('/dame_cajeros_disponibles','gestionUsuarios_controlador@dame_cajeros_disponibles');
});



Route::middleware(['auth'])->prefix('inventario')->group(function()
{
    Route::get('/','inventario_controlador@index');
    Route::get('/{id}', 'inventario_controlador@inventario_por_local');
    Route::post('/traslado', 'inventario_controlador@traslado');
    Route::get('/damerepuesto/{id}','inventario_controlador@damerepuesto');
    Route::get('/ordenar/{local_id}/{orden_id}','inventario_controlador@ordenar');
    
});

Route::get('/','servidorlocalcontrolador@login_server');
Route::post('/loginserver','servidorlocalcontrolador@setUser')->name('loginserver');
Route::get('/habilitar_usuarios','servidorlocalcontrolador@habilitar_usuarios');
Route::get('/deshabilitar_usuarios','servidorlocalcontrolador@deshabilitar_usuarios');
Route::get('/habilitar_jose','servidorlocalcontrolador@habilitar_jose');
Route::get('/deshabilitar_jose','servidorlocalcontrolador@deshabilitar_jose');

Route::get('/solicitudes','inventario_controlador@pedidos_a_bodega_vista')->middleware('auth');


route::post('/procesar_solicitud','inventario_controlador@procesar_solicitud')->middleware('auth');
Route::get('/dame_solicitudes','inventario_controlador@dame_solicitudes')->middleware('auth');
Route::get('/eliminar_solicitud/{id}','inventario_controlador@eliminar_solicitud')->middleware('auth');

Route::get('/bienvenida',function(){

    return view('bienvenida');

});



Route::get('/timbre/{doc}/{num}', 'imprimir_controlador@dame_timbre')->middleware('auth');



Route::get('/home', 'HomeController@index')->name('home');


Route::get('/ticket','inventario_controlador@ticket');
Route::post('/guardar_ticket','inventario_controlador@guardar_ticket');
Route::get('/eliminarticket/{id}','inventario_controlador@eliminarticket');
Route::get('/dameticket/{id}','inventario_controlador@dameticket');

Route::get('/damesesion','HomeController@dame_sesion')->middleware('auth');

Route::get('/expirósesión','HomeController@autenticado')->middleware('auth');

Route::get('/cambiarclave','HomeController@form_cambiar_clave')->middleware('auth');

Route::get('/cambiarclave/{id}','HomeController@form_cambiar_clave_usuario')->middleware('auth');

Route::post('/cambiarclave','HomeController@cambiar_clave')->name('cambiarclave')->middleware('auth');

Route::post('/cambiarclaveusuario','HomeController@cambiar_clave_usuario')->name('cambiarclaveusuario')->middleware('auth');

Route::post('/clave','HomeController@dame_clave')->middleware('auth');
Route::post('/clave_descuento','HomeController@dame_clave_descuento')->middleware('auth');
Route::get('/cambiar_passwords','HomeController@cambiar_passwords');


//Pruebas de búsqueda

Route::get('/pruebas_buscar',function(){

    return view('pruebas.buscar');

});



Route::get('/probar_codint','ventas_controlador@probar_codint');

Route::get('/probar_codprov','ventas_controlador@probar_codprov');

Route::get('/probar_codoem','ventas_controlador@probar_codoem');

Route::get('/probar_codfam','ventas_controlador@probar_codfam');

Route::get('/probar_codfab','ventas_controlador@probar_codfab');

Route::get('/probar_nomfab','ventas_controlador@probar_nomfab');

Route::get('/probar_marveh','ventas_controlador@probar_marveh');

Route::get('/probar_modveh','ventas_controlador@probar_modveh');

Route::get('/probar_descrip','ventas_controlador@probar_descrip');

Route::get('/phpinfo',function(){

    phpinfo();

});

Route::post('/giftcard',[imprimir_controlador::class, 'giftcard']);
Route::post('/imprimir_devolucion',[imprimir_controlador::class, 'imprimir_devolucion']);
Route::post('/solicitud_traspaso',[imprimir_controlador::class, 'solicitud_traspaso']);

Route::get('/revisar_carrito','ventas_controlador@revisar_carrito');
Route::get('/revisar_solicitud','inventario_controlador@revisar_solicitud');

//Enviar correos

Route::post('/enviarcorreo','correos_controlador@enviar_correo')->middleware('auth');


//Tipo Documentos

Route::get('/dame_tipo_documentos','tipo_documentos_controlador@dame_tipos')->middleware('auth');



//rutas errores

Route::get('/noautenticado',function(){
    return view('errors.noautenticado');
});

Route::get('/sesionexpiro',function(){
    return view('errors.sesionexpiro');
});

Route::get('/error',function(){
    return view('errors.error_general');
});

Route::get('/semilla','nuevo_controlador@dameSemilla');
Route::get('/envia','nuevo_controlador@envia');
Route::get('/envia2','nuevo_controlador@envia2');
Route::post('/recibe','nuevo_controlador@recibe');

Route::post('import-oems-excel','HomeController@importExcel')->name('oems.import.excel');

Route::get('/home', 'HomeController@index')->name('home');
// Imprimir_controlador o repuesto_codigo_controlador
Route::get('/generate-barcode/{id}', [imprimir_controlador::class, 'imprimir_codebar'])->name('generate.barcode');
Route::get('/generate-qrcode', [imprimir_controlador::class, 'imprimir_qr'])->name('generate.qrcode');
Route::get('/barcode', [repuesto_codigo_controlador::class, 'buscar_repuesto_vista'])->name('buscar.barcode');
Route::post('/buscar-barcode', [repuesto_codigo_controlador::class, 'buscar_repuesto']);


//Ruta para verificar usuario ingresando al servidor
Route::post('/validar_usuario','HomeController@validar_usuario')->name('validar_usuario');

Route::get('/generate-pdf','pdf_controlador@generatePdf');


Auth::routes(); //no borrar y siempre al final