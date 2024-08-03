<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\user_server;
use App\rol;
use App\cajero;
use App\permisos;
use App\role_has_permissions;
use App\users_has_permissions;
use App\permissions_detail;
use App\registro_login;
use Carbon\Carbon; // para tratamiento de fechas

class gestionUsuarios_controlador extends Controller
{
    public function index(){
        //El valor 2 en activo significa que el usuario no pertenece al sistema pero se mantienen sus datos para cualquier fin
        $users = User::where('id','<>',8)->where('id','<>',6)->where('id','<>',9)->where('role_id','!=',20)->where('activo','<>',2)->orderBy('name')->get();
        $roles = rol::all();
        // $opts = ["Ventas (Fact-Boleta)","Notas de crédito","Notas de débito","Facturas de Compra","Listar Facturas (Compras)","Cargar Folios","Estado de Envíos","Ambiente Certificación","RCOF Boletas","Libro Ventas","Libro Compras"];
        $permisos = permisos::all();
        $user = Auth::user();
        $cajeros = cajero::select('users.name','cajeros.id_usuario')->join('users','cajeros.id_usuario','users.id')->get();
        
        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('manten.usuarios_gestion_nueva',['users' => $users, 'roles' => $roles,'permisos' => $permisos,'cajeros' => $cajeros]);
        }else{
            return redirect('/home');
        }
        
        

    }

    public function confirmaringreso($path){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 8 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == $path){
                        return true;
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return true;
        } else {
            return false;
        }
    }

    public function dame_cajeros_disponibles(){
        $cajeros = cajero::select('users.name','cajeros.id_usuario','users.id','users.activo')
                            ->join('users','cajeros.id_usuario','users.id')
                            ->get();
        return $cajeros;
    }

    public function eliminar_cajero_disponible($id_usuario){
        $cajero = cajero::where('id_usuario',$id_usuario)->first();
        $cajero->delete();
        $cajeros = $this->dame_cajeros_disponibles();
        return $cajeros;
    }

    public function guardar_cajeros_disponibles(Request $req){
        try {
            $cajeros = $req->detalles;
            
                foreach($cajeros as $cajero){
                    $value = $this->revisar_cajero($cajero);
                    if($value){
                        $nuevo_cajero = new cajero;
                        $nuevo_cajero->id_usuario = $cajero;
                        $nuevo_cajero->fecha_emision = Carbon::today()->toDateString();
                        $nuevo_cajero->save();
                    }else{
                        return 'Ya existe ese cajero';
                    }
                    
                }
                $cajeros = $this->dame_cajeros_disponibles();
                return ['OK',$cajeros];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    private function revisar_cajero($cajero_id){
        $cajeros = cajero::all();
        foreach($cajeros as $c){
            if($c->id_usuario == $cajero_id){
                return false;
            }
        }

        return true;
    }

    public function cambiar_div_vista($permisoId){
        $id = $permisoId;
        if($id == 3) $opciones = ['Ventas','Cotizaciones','Consignaciones','Busqueda expres','Busqueda cliente','Borrar carrito','Guardar carrito','Recuperar carrito','Transferir carrito','Solo transferir carrito','Nota de crédito','Nota de débito','Arqueo de caja','Pedidos','Vales de mercadería','Vales de consignación','Agregar referencia','Agregar repuesto expres'];
        if($id == 4) $opciones = ['Facturas de compra','Listas facturas','Buscar repuestos','Stock de repuestos','Ingresados vs vendidos','Guía de despacho','Recepción de guía despacho','Traspaso de mercadería','Recepción de mercadería','Devolución de mercadería','Vista Ofertas','Orden de transporte','Inventario por tienda','Solicitudes','Editar factura de ingreso','Mostrar ofertas web','Ultimos repuestos','Kit de repuestos','Evaluar cotizaciones'];
        if($id == 5) $opciones = ['Cargar folios','Anulación de folios','Estado DTE'];
        if($id == 6) $opciones = ['Marca vehículo','Modelo vehículo','Roles de usuario','Familia de repuestos','Marca de repuestos','Modificar repuestos','Catálogo de repuestos','Paises','Proveedores','Formas de pago','Límites de créditos','Días de crédito','Clientes','Estado de cuenta cliente','Cliente express','Parametros','Repuestos relacionados','Usuarios'];
        if($id == 7) $opciones = ['RCOF Boletas','Libro ventas','Libro compras'];
        if($id == 8) $opciones = ['Ventas diarias','Detalle ventas','Desempeño mensual','Documentos generados','Buscar documentos','Documentos Transbank','Documentos Getnet'];
        if($id == 9) $opciones = ['Buscar por medidas','Modificar precio','Actualizar precio'];
        $v = view('fragm.div_permisos',compact('id','opciones'));
        return $v;
    }

    public function saveUser(Request $request){
        
try {
    $reglas=array(
        'rut'=>'required|max:10|unique:users,rut'
    );

    $mensajes=array(
        'rut.required'=>'Debe Ingresar un rut',
        'rut.max'=>'El rut debe tener como máximo 10 caracteres.',
        'rut.unique'=>'El rut ya existe.'
    );

    $name = $request->input('name');
    $rut = $request->input('rut');

    $validameRut = $this->validarRut($rut);
    if(!$validameRut) return redirect('usuarios/crear')->with('error', 'Rut inválido');
    $telefono = $request->input('telefono');
    $email = $request->input('email');
    $password = $request->input('password');
    $image_path = $request->file('avatar');
    $role_id = $request->input('role');

    $password_encrypt = Hash::make($password);

    if($image_path){

        //Poner nombre único
        $image_path_name = time().$image_path->getClientOriginalName();
        // Guardar en la carpeta storage
        Storage::disk('users')->put($image_path_name, File::get($image_path));
    
    }
    
    $this->validate($request,$reglas,$mensajes);
    
    $user =  User::create([
        'name' => $name,
        'rut' => $rut,
        'telefono' => $telefono,
        'email' => $email,
        'password' => $password_encrypt,
        'image_path' => $image_path_name,
        'role_id' => $role_id
    ]);

    $user = $this->dame_ultimo_usuario();
    //Bodeguero
    if($role_id == 11){
        $p1 = new permissions_detail;
        $p1->permission_id = 4;
        $p1->descripcion = 'Facturas de compra';
        $p1->path_ruta = '/factuprodu/crear';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 4;
        $p2->descripcion = 'Listas facturas';
        $p2->path_ruta = '/compras/listar';
        $p2->usuarios_id = $user->id;
        $p2->save();

        $p3 = new permissions_detail;
        $p3->permission_id = 4;
        $p3->descripcion = 'Guía de despacho';
        $p3->path_ruta = '/guiadespacho';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 4;
        $p4->descripcion = 'Traspaso de mercadería';
        $p4->path_ruta = '/guiadespacho/traspaso_mercaderia';
        $p4->usuarios_id = $user->id;
        $p4->save();

        $p5 = new permissions_detail;
        $p5->permission_id = 4;
        $p5->descripcion = 'Recepción de mercadería';
        $p5->path_ruta = '/guiadespacho/recepcion_mercaderia';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 4;
        $p6->descripcion = 'Devolución de mercadería';
        $p6->path_ruta = '/guiadespacho/devolucion_mercaderia';
        $p6->usuarios_id = $user->id;
        $p6->save();

        $p7 = new permissions_detail;
        $p7->permission_id = 4;
        $p7->descripcion = 'Orden de transporte';
        $p7->path_ruta = '/ot';
        $p7->usuarios_id = $user->id;
        $p7->save();

        $p8 = new permissions_detail;
        $p8->permission_id = 9;
        $p8->descripcion = 'Buscar por medidas';
        $p8->path_ruta = '/repuesto/buscar-medida';
        $p8->usuarios_id = $user->id;
        $p8->save();


    }
    //Jefe de bodega
    if($role_id == 12){
        $p1 = new permissions_detail;
        $p1->permission_id = 4;
        $p1->descripcion = 'Facturas de compra';
        $p1->path_ruta = '/factuprodu/crear';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 4;
        $p2->descripcion = 'Listas facturas';
        $p2->path_ruta = '/compras/listar';
        $p2->usuarios_id = $user->id;
        $p2->save();

        
        $p3 = new permissions_detail;
        $p3->permission_id = 4;
        $p3->descripcion = 'Guía de despacho';
        $p3->path_ruta = '/guiadespacho';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 4;
        $p4->descripcion = 'Traspaso de mercadería';
        $p4->path_ruta = '/guiadespacho/traspaso_mercaderia';
        $p4->usuarios_id = $user->id;
        $p4->save();

        $p5 = new permissions_detail;
        $p5->permission_id = 4;
        $p5->descripcion = 'Recepción de mercadería';
        $p5->path_ruta = '/guiadespacho/recepcion_mercaderia';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 4;
        $p6->descripcion = 'Devolución de mercadería';
        $p6->path_ruta = '/guiadespacho/devolucion_mercaderia';
        $p6->usuarios_id = $user->id;
        $p6->save();

        $p7 = new permissions_detail;
        $p7->permission_id = 4;
        $p7->descripcion = 'Orden de transporte';
        $p7->path_ruta = '/ot';
        $p7->usuarios_id = $user->id;
        $p7->save();

        $p8 = new permissions_detail;
        $p8->permission_id = 9;
        $p8->descripcion = 'Buscar por medidas';
        $p8->path_ruta = '/repuesto/buscar-medida';
        $p8->usuarios_id = $user->id;
        $p8->save();
    }
    //Vendedor
    if($role_id == 13){
        $p1 = new permissions_detail;
        $p1->permission_id = 3;
        $p1->descripcion = 'Ventas';
        $p1->path_ruta = '/ventas';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 9;
        $p2->descripcion = 'Buscar por medidas';
        $p2->path_ruta = '/repuesto/buscar-medida';
        $p2->usuarios_id = $user->id;
        $p2->save();

        $p3 = new permissions_detail;
        $p3->permission_id = 3;
        $p3->descripcion = 'Cotizaciones';
        $p3->path_ruta = '/cotizaciones';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 3;
        $p4->descripcion = 'Consignaciones';
        $p4->path_ruta = '/consignaciones';
        $p4->usuarios_id = $user->id;
        $p4->save();

        $p5 = new permissions_detail;
        $p5->permission_id = 3;
        $p5->descripcion = 'Busqueda expres';
        $p5->path_ruta = '/busqueda_expres';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 3;
        $p6->descripcion = 'Busqueda cliente';
        $p6->path_ruta = '/busqueda_cliente';
        $p6->usuarios_id = $user->id;
        $p6->save();
    }
    //Cajero
    if($role_id == 14){
        $p1 = new permissions_detail;
        $p1->permission_id = 3;
        $p1->descripcion = 'Ventas';
        $p1->path_ruta = '/ventas';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 9;
        $p2->descripcion = 'Buscar por medidas';
        $p2->path_ruta = '/repuesto/buscar-medida';
        $p2->usuarios_id = $user->id;
        $p2->save();

        $p3 = new permissions_detail;
        $p3->permission_id = 3;
        $p3->descripcion = 'Nota de crédito';
        $p3->path_ruta = '/notacredito';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 3;
        $p4->descripcion = 'Arqueo de caja';
        $p4->path_ruta = '/ventas/arqueocaja';
        $p4->usuarios_id = $user->id;
        $p4->save();

        $p5 = new permissions_detail;
        $p5->permission_id = 3;
        $p5->descripcion = 'Pedidos';
        $p5->path_ruta = '/ventas/pedidos_nuevo';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 3;
        $p6->descripcion = 'Vales de mercadería';
        $p6->path_ruta = '/ventas/vale_por_mercaderia';
        $p6->usuarios_id = $user->id;
        $p6->save();

        $p7 = new permissions_detail;
        $p7->permission_id = 4;
        $p7->descripcion = 'Devolución de mercadería';
        $p7->path_ruta = '/guiadespacho/devolucion_mercaderia';
        $p7->usuarios_id = $user->id;
        $p7->save();

        $p8 = new permissions_detail;
        $p8->permission_id = 5;
        $p8->descripcion = 'Estado DTE';
        $p8->path_ruta = '/sii/estadodte';
        $p8->usuarios_id = $user->id;
        $p8->save();

        $p9 = new permissions_detail;
        $p9->permission_id = 6;
        $p9->descripcion = 'Clientes';
        $p9->path_ruta = '/clientes';
        $p9->usuarios_id = $user->id;
        $p9->save();

        $p10 = new permissions_detail;
        $p10->permission_id = 3;
        $p10->descripcion = 'Cotizaciones';
        $p10->path_ruta = '/cotizaciones';
        $p10->usuarios_id = $user->id;
        $p10->save();

        $p11 = new permissions_detail;
        $p11->permission_id = 3;
        $p11->descripcion = 'Consignaciones';
        $p11->path_ruta = '/consignaciones';
        $p11->usuarios_id = $user->id;
        $p11->save();

        $p12 = new permissions_detail;
        $p12->permission_id = 3;
        $p12->descripcion = 'Busqueda expres';
        $p12->path_ruta = '/busqueda_expres';
        $p12->usuarios_id = $user->id;
        $p12->save();

        $p13 = new permissions_detail;
        $p13->permission_id = 3;
        $p13->descripcion = 'Agregar referencia';
        $p13->path_ruta = '/agregar_referencia';
        $p13->usuarios_id = $user->id;
        $p13->save();

        $p14 = new permissions_detail;
        $p14->permission_id = 3;
        $p14->descripcion = 'Busqueda cliente';
        $p14->path_ruta = '/busqueda_cliente';
        $p14->usuarios_id = $user->id;
        $p14->save();

        $p15 = new permissions_detail;
        $p15->permission_id = 3;
        $p15->descripcion = 'Borrar carrito';
        $p15->path_ruta = '/borrar_carrito';
        $p15->usuarios_id = $user->id;
        $p15->save();

        $p16 = new permissions_detail;
        $p16->permission_id = 3;
        $p16->descripcion = 'Recuperar carrito';
        $p16->path_ruta = '/recuperar_carrito';
        $p16->usuarios_id = $user->id;
        $p16->save();
    }

    // Bodega - venta
    if($role_id == 16){
        $p1 = new permissions_detail;
        $p1->permission_id = 3;
        $p1->descripcion = 'Ventas';
        $p1->path_ruta = '/ventas';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 4;
        $p2->descripcion = 'Facturas de compra';
        $p2->path_ruta = '/factuprodu/crear';
        $p2->usuarios_id = $user->id;
        $p2->save();

        $p3 = new permissions_detail;
        $p3->permission_id = 4;
        $p3->descripcion = 'Listas facturas';
        $p3->path_ruta = '/compras/listar';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 4;
        $p4->descripcion = 'Guía de despacho';
        $p4->path_ruta = '/guiadespacho';
        $p4->usuarios_id = $user->id;
        $p4->save();

        $p5 = new permissions_detail;
        $p5->permission_id = 4;
        $p5->descripcion = 'Traspaso de mercadería';
        $p5->path_ruta = '/guiadespacho/traspaso_mercaderia';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 4;
        $p6->descripcion = 'Recepción de mercadería';
        $p6->path_ruta = '/guiadespacho/recepcion_mercaderia';
        $p6->usuarios_id = $user->id;
        $p6->save();

        $p7 = new permissions_detail;
        $p7->permission_id = 4;
        $p7->descripcion = 'Devolución de mercadería';
        $p7->path_ruta = '/guiadespacho/devolucion_mercaderia';
        $p7->usuarios_id = $user->id;
        $p7->save();

        $p8 = new permissions_detail;
        $p8->permission_id = 4;
        $p8->descripcion = 'Orden de transporte';
        $p8->path_ruta = '/ot';
        $p8->usuarios_id = $user->id;
        $p8->save();

        $p9 = new permissions_detail;
        $p9->permission_id = 9;
        $p9->descripcion = 'Buscar por medidas';
        $p9->path_ruta = '/repuesto/buscar-medida';
        $p9->usuarios_id = $user->id;
        $p9->save();

        $p10 = new permissions_detail;
        $p10->permission_id = 6;
        $p10->descripcion = 'Clientes';
        $p10->path_ruta = '/clientes';
        $p10->usuarios_id = $user->id;
        $p10->save();

        $p11 = new permissions_detail;
        $p11->permission_id = 3;
        $p11->descripcion = 'Busqueda cliente';
        $p11->path_ruta = '/busqueda_cliente';
        $p11->usuarios_id = $user->id;
        $p11->save();

        
        $p11 = new permissions_detail;
        $p11->permission_id = 3;
        $p11->descripcion = 'Busqueda cliente';
        $p11->path_ruta = '/busqueda_cliente';
        $p11->usuarios_id = $user->id;
        $p11->save();

        $p12 = new permissions_detail;
        $p12->permission_id = 3;
        $p12->descripcion = 'Busqueda expres';
        $p12->path_ruta = '/busqueda_expres';
        $p12->usuarios_id = $user->id;
        $p12->save();

        $p13 = new permissions_detail;
        $p13->permission_id = 3;
        $p13->descripcion = 'Borrar carrito';
        $p13->path_ruta = '/borrar_carrito';
        $p13->usuarios_id = $user->id;
        $p13->save();

        $p14 = new permissions_detail;
        $p14->permission_id = 3;
        $p14->descripcion = 'Transferir carrito';
        $p14->path_ruta = '/transferir_carrito';
        $p14->usuarios_id = $user->id;
        $p14->save();

        $p15 = new permissions_detail;
        $p15->permission_id = 3;
        $p15->descripcion = 'Recuperar carrito';
        $p15->path_ruta = '/recuperar_carrito';
        $p15->usuarios_id = $user->id;
        $p15->save();
    }

    //Contabilidad
    if($role_id == 17){
        $p1 = new permissions_detail;
        $p1->permission_id = 5;
        $p1->descripcion = 'Cargar folios';
        $p1->path_ruta = '/sii/cargarfolios';
        $p1->usuarios_id = $user->id;
        $p1->save();

        $p2 = new permissions_detail;
        $p2->permission_id = 5;
        $p2->descripcion = 'Estado DTE';
        $p2->path_ruta = '/sii/estadodte';
        $p2->usuarios_id = $user->id;
        $p2->save();

        $p3 = new permissions_detail;
        $p3->permission_id = 7;
        $p3->descripcion = 'RCOF Boletas';
        $p3->path_ruta = '/rcof';
        $p3->usuarios_id = $user->id;
        $p3->save();

        $p4 = new permissions_detail;
        $p4->permission_id = 7;
        $p4->descripcion = 'Libro ventas';
        $p4->path_ruta = '/libro/ventas';
        $p4->usuarios_id = $user->id;
        $p4->save();
        
        $p5 = new permissions_detail;
        $p5->permission_id = 7;
        $p5->descripcion = 'Libro compras';
        $p5->path_ruta = '/libro/compras';
        $p5->usuarios_id = $user->id;
        $p5->save();

        $p6 = new permissions_detail;
        $p6->permission_id = 9;
        $p6->descripcion = 'Buscar por medidas';
        $p6->path_ruta = '/repuesto/buscar-medida';
        $p6->usuarios_id = $user->id;
        $p6->save();
    }

    //Estandar
    if($role_id == 19){
        $p1 = new permissions_detail;
        $p1->permission_id = 9;
        $p1->descripcion = 'Buscar por medidas';
        $p1->path_ruta = '/repuesto/buscar-medida';
        $p1->usuarios_id = $user->id;
        $p1->save();
    }

    return redirect('/usuarios');
} catch (\Exception $e) {
    //throw $th;
    return $e->getMessage();
}
        
    }

    public function dame_ultimo_usuario(){
        $ultimo_usuario = User::latest()->first();
        return $ultimo_usuario;
    }

    //Función pública para recuperar avatar del usuario

    public function getAvatar($filename){
        
        $file = Storage::disk('users')->get($filename);

        return new Response($file,200);
    }

    public function edit($id){
        $user = User::find($id);
        $roles = rol::all();
        return view('users.edit',['user' => $user, 'roles' => $roles]);
    }

    public function update(Request $request){
        $image_path = $request->file('avatar');
        
        if($image_path){

            //Poner nombre único
            $image_path_name = time().$image_path->getClientOriginalName();
            
            // Guardar en la carpeta storage
            Storage::disk('users')->put($image_path_name, File::get($image_path));
    
        }
        $name = $request->input('name');
        
        $rut = $request->input('rut');
        
        $telefono = $request->input('telefono');
        $email = $request->input('email');
        $userId = $request->input('userId');
        
        $user = User::find($userId);
        
        $user->name = $name;
        $user->rut = $rut;
        $user->telefono = $telefono;
        $user->email = $email;
        $user->image_path = $image_path_name;

        $user->save();

        return redirect('home')->with('status', 'Profile updated!');
    }

    public function create(){
        $roles = rol::where('nombrerol','<>','web')->get();
        return view('users.register',['roles' => $roles]);
    }

    public function validarRut($rut){
        // Eliminar caracteres no válidos (puntos y guión)
        $rut = str_replace(['.', '-'], '', $rut);

        // Extraer el dígito verificador
        $dv = substr($rut, -1);
        $rut = substr($rut, 0, -1);

        // Calcular el dígito verificador esperado
        $s = 1;
        for ($m = 0; $rut != 0; $rut /= 10) {
            $s = ($s + $rut % 10 * (9 - $m++ % 6)) % 11;
        }
        $dv_esperado = chr($s ? $s + 47 : 75);

        // Comparar el dígito verificador ingresado con el esperado
        if ($dv === $dv_esperado) {
            return true;
        } else {
            return false;
        }
    }

    public function getUser($id){
        try {
            $user = User::find($id);
            $permisos = permisos::all();
            $rol = $user->rol->nombrerol;
            // variable donde se almacena los permisos de cada usuario
            $u_h_p = permissions_detail::where('usuarios_id',$user->id)->get();
            $values = [$user, $rol, $permisos,$u_h_p];
            return $values;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function delete($id){
        $user = User::find($id);
        $permisos = permissions_detail::where('usuarios_id',$id)->get();
        // Eliminamos todos los permisos que tenga el usuario
        foreach($permisos as $p){
            $p->delete();
        } 
        // En vez de eliminar al usuario lo desactivamos para no perder sus datos
        // Desde hoy 18 de Julio del 2023 cada vez que se eliminé un usuario pasara al activo = 2 para no perder sus datos
        $user->activo = 2;
        $user->update();


        return $user->name;
    }

    public function cambioRol(Request $request){
        $user = User::find($request->user_id);

        $user->role_id = $request->role_id;
        //Eliminamos todos los permisos asignados anteriormente
        $permisos = permissions_detail::where('usuarios_id',$user->id)->get();

        foreach ($permisos as $p) $p->delete();
        //Perfil de Bodeguero
        if($request->role_id == 11){
            $p1 = new permissions_detail;
            $p1->permission_id = 4;
            $p1->descripcion = 'Facturas de compra';
            $p1->path_ruta = '/factuprodu/crear';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 4;
            $p2->descripcion = 'Listas facturas';
            $p2->path_ruta = '/compras/listar';
            $p2->usuarios_id = $user->id;
            $p2->save();

            $p3 = new permissions_detail;
            $p3->permission_id = 4;
            $p3->descripcion = 'Guía de despacho';
            $p3->path_ruta = '/guiadespacho';
            $p3->usuarios_id = $user->id;
            $p3->save();

            $p4 = new permissions_detail;
            $p4->permission_id = 4;
            $p4->descripcion = 'Traspaso de mercadería';
            $p4->path_ruta = '/guiadespacho/traspaso_mercaderia';
            $p4->usuarios_id = $user->id;
            $p4->save();

            $p5 = new permissions_detail;
            $p5->permission_id = 4;
            $p5->descripcion = 'Recepción de mercadería';
            $p5->path_ruta = '/guiadespacho/recepcion_mercaderia';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 4;
            $p6->descripcion = 'Devolución de mercadería';
            $p6->path_ruta = '/guiadespacho/devolucion_mercaderia';
            $p6->usuarios_id = $user->id;
            $p6->save();

            $p7 = new permissions_detail;
            $p7->permission_id = 4;
            $p7->descripcion = 'Orden de transporte';
            $p7->path_ruta = '/ot';
            $p7->usuarios_id = $user->id;
            $p7->save();

            $p8 = new permissions_detail;
            $p8->permission_id = 9;
            $p8->descripcion = 'Buscar por medidas';
            $p8->path_ruta = '/repuesto/buscar-medida';
            $p8->usuarios_id = $user->id;
            $p8->save();

            $p9 = new permissions_detail;
            $p9->permission_id = 4;
            $p9->descripcion = 'Evaluar cotizaciones';
            $p9->path_ruta = '/ventas/cotizaciones';
            $p9->usuarios_id = $user->id;
            $p9->save();
        }
        //Jefe de bodega
        if($request->role_id == 12){
            $p1 = new permissions_detail;
            $p1->permission_id = 4;
            $p1->descripcion = 'Facturas de compra';
            $p1->path_ruta = '/factuprodu/crear';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 4;
            $p2->descripcion = 'Listas facturas';
            $p2->path_ruta = '/compras/listar';
            $p2->usuarios_id = $user->id;
            $p2->save();

            
            $p3 = new permissions_detail;
            $p3->permission_id = 4;
            $p3->descripcion = 'Guía de despacho';
            $p3->path_ruta = '/guiadespacho';
            $p3->usuarios_id = $user->id;
            $p3->save();

            $p4 = new permissions_detail;
            $p4->permission_id = 4;
            $p4->descripcion = 'Traspaso de mercadería';
            $p4->path_ruta = '/guiadespacho/traspaso_mercaderia';
            $p4->usuarios_id = $user->id;
            $p4->save();

            $p5 = new permissions_detail;
            $p5->permission_id = 4;
            $p5->descripcion = 'Recepción de mercadería';
            $p5->path_ruta = '/guiadespacho/recepcion_mercaderia';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 4;
            $p6->descripcion = 'Devolución de mercadería';
            $p6->path_ruta = '/guiadespacho/devolucion_mercaderia';
            $p6->usuarios_id = $user->id;
            $p6->save();

            $p7 = new permissions_detail;
            $p7->permission_id = 4;
            $p7->descripcion = 'Orden de transporte';
            $p7->path_ruta = '/ot';
            $p7->usuarios_id = $user->id;
            $p7->save();

            $p8 = new permissions_detail;
            $p8->permission_id = 9;
            $p8->descripcion = 'Buscar por medidas';
            $p8->path_ruta = '/repuesto/buscar-medida';
            $p8->usuarios_id = $user->id;
            $p8->save();
        }
        //Vendedor
        if($request->role_id == 13){
            $p1 = new permissions_detail;
            $p1->permission_id = 3;
            $p1->descripcion = 'Ventas';
            $p1->path_ruta = '/ventas';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 9;
            $p2->descripcion = 'Buscar por medidas';
            $p2->path_ruta = '/repuesto/buscar-medida';
            $p2->usuarios_id = $user->id;
            $p2->save();

            $p3 = new permissions_detail;
            $p3->permission_id = 3;
            $p3->descripcion = 'Cotizaciones';
            $p3->path_ruta = '/cotizaciones';
            $p3->usuarios_id = $user->id;
            $p3->save();
    
            $p4 = new permissions_detail;
            $p4->permission_id = 3;
            $p4->descripcion = 'Consignaciones';
            $p4->path_ruta = '/consignaciones';
            $p4->usuarios_id = $user->id;
            $p4->save();

            $p5 = new permissions_detail;
            $p5->permission_id = 3;
            $p5->descripcion = 'Busqueda expres';
            $p5->path_ruta = '/busqueda_expres';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 3;
            $p6->descripcion = 'Busqueda cliente';
            $p6->path_ruta = '/busqueda_cliente';
            $p6->usuarios_id = $user->id;
            $p6->save();

            $p7 = new permissions_detail;
            $p7->permission_id = 3;
            $p7->descripcion = 'Borrar carrito';
            $p7->path_ruta = '/borrar_carrito';
            $p7->usuarios_id = $user->id;
            $p7->save();

            $p8 = new permissions_detail;
            $p8->permission_id = 3;
            $p8->descripcion = 'Guardar carrito';
            $p8->path_ruta = '/guardar_carrito';
            $p8->usuarios_id = $user->id;
            $p8->save();

            $p9 = new permissions_detail;
            $p9->permission_id = 3;
            $p9->descripcion = 'Transferir carrito';
            $p9->path_ruta = '/transferir_carrito';
            $p9->usuarios_id = $user->id;
            $p9->save();

            $p10 = new permissions_detail;
            $p10->permission_id = 3;
            $p10->descripcion = 'Recuperar carrito';
            $p10->path_ruta = '/recuperar_carrito';
            $p10->usuarios_id = $user->id;
            $p10->save();
        }
        //Cajero
        if($request->role_id == 14){
            $p1 = new permissions_detail;
            $p1->permission_id = 3;
            $p1->descripcion = 'Ventas';
            $p1->path_ruta = '/ventas';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 9;
            $p2->descripcion = 'Buscar por medidas';
            $p2->path_ruta = '/repuesto/buscar-medida';
            $p2->usuarios_id = $user->id;
            $p2->save();

            $p3 = new permissions_detail;
            $p3->permission_id = 3;
            $p3->descripcion = 'Nota de crédito';
            $p3->path_ruta = '/notacredito';
            $p3->usuarios_id = $user->id;
            $p3->save();

            $p4 = new permissions_detail;
            $p4->permission_id = 3;
            $p4->descripcion = 'Arqueo de caja';
            $p4->path_ruta = '/ventas/arqueocaja';
            $p4->usuarios_id = $user->id;
            $p4->save();

            $p5 = new permissions_detail;
            $p5->permission_id = 3;
            $p5->descripcion = 'Pedidos';
            $p5->path_ruta = '/ventas/pedidos_nuevo';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 3;
            $p6->descripcion = 'Vales de mercadería';
            $p6->path_ruta = '/ventas/vale_por_mercaderia';
            $p6->usuarios_id = $user->id;
            $p6->save();

            $p7 = new permissions_detail;
            $p7->permission_id = 4;
            $p7->descripcion = 'Devolución de mercadería';
            $p7->path_ruta = '/guiadespacho/devolucion_mercaderia';
            $p7->usuarios_id = $user->id;
            $p7->save();

            $p8 = new permissions_detail;
            $p8->permission_id = 5;
            $p8->descripcion = 'Estado DTE';
            $p8->path_ruta = '/sii/estadodte';
            $p8->usuarios_id = $user->id;
            $p8->save();

            $p9 = new permissions_detail;
            $p9->permission_id = 6;
            $p9->descripcion = 'Clientes';
            $p9->path_ruta = '/clientes';
            $p9->usuarios_id = $user->id;
            $p9->save();

            $p10 = new permissions_detail;
            $p10->permission_id = 3;
            $p10->descripcion = 'Cotizaciones';
            $p10->path_ruta = '/cotizaciones';
            $p10->usuarios_id = $user->id;
            $p10->save();
    
            $p11 = new permissions_detail;
            $p11->permission_id = 3;
            $p11->descripcion = 'Consignaciones';
            $p11->path_ruta = '/consignaciones';
            $p11->usuarios_id = $user->id;
            $p11->save();

            $p12 = new permissions_detail;
            $p12->permission_id = 3;
            $p12->descripcion = 'Busqueda expres';
            $p12->path_ruta = '/busqueda_expres';
            $p12->usuarios_id = $user->id;
            $p12->save();
    
            $p13 = new permissions_detail;
            $p13->permission_id = 3;
            $p13->descripcion = 'Agregar referencia';
            $p13->path_ruta = '/agregar_referencia';
            $p13->usuarios_id = $user->id;
            $p13->save();

            $p14 = new permissions_detail;
            $p14->permission_id = 3;
            $p14->descripcion = 'Busqueda cliente';
            $p14->path_ruta = '/busqueda_cliente';
            $p14->usuarios_id = $user->id;
            $p14->save();

            $p15 = new permissions_detail;
            $p15->permission_id = 3;
            $p15->descripcion = 'Borrar carrito';
            $p15->path_ruta = '/borrar_carrito';
            $p15->usuarios_id = $user->id;
            $p15->save();

            $p16 = new permissions_detail;
            $p16->permission_id = 3;
            $p16->descripcion = 'Guardar carrito';
            $p16->path_ruta = '/guardar_carrito';
            $p16->usuarios_id = $user->id;
            $p16->save();

            $p17 = new permissions_detail;
            $p17->permission_id = 3;
            $p17->descripcion = 'Recuperar carrito';
            $p17->path_ruta = '/recuperar_carrito';
            $p17->usuarios_id = $user->id;
            $p17->save();
        }

        // Bodega - venta
        if($request->role_id == 16){
            $p1 = new permissions_detail;
            $p1->permission_id = 3;
            $p1->descripcion = 'Ventas';
            $p1->path_ruta = '/ventas';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 4;
            $p2->descripcion = 'Facturas de compra';
            $p2->path_ruta = '/factuprodu/crear';
            $p2->usuarios_id = $user->id;
            $p2->save();

            $p3 = new permissions_detail;
            $p3->permission_id = 4;
            $p3->descripcion = 'Listas facturas';
            $p3->path_ruta = '/compras/listar';
            $p3->usuarios_id = $user->id;
            $p3->save();

            $p4 = new permissions_detail;
            $p4->permission_id = 4;
            $p4->descripcion = 'Guía de despacho';
            $p4->path_ruta = '/guiadespacho';
            $p4->usuarios_id = $user->id;
            $p4->save();

            $p5 = new permissions_detail;
            $p5->permission_id = 4;
            $p5->descripcion = 'Traspaso de mercadería';
            $p5->path_ruta = '/guiadespacho/traspaso_mercaderia';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 4;
            $p6->descripcion = 'Recepción de mercadería';
            $p6->path_ruta = '/guiadespacho/recepcion_mercaderia';
            $p6->usuarios_id = $user->id;
            $p6->save();

            $p7 = new permissions_detail;
            $p7->permission_id = 4;
            $p7->descripcion = 'Devolución de mercadería';
            $p7->path_ruta = '/guiadespacho/devolucion_mercaderia';
            $p7->usuarios_id = $user->id;
            $p7->save();

            $p8 = new permissions_detail;
            $p8->permission_id = 4;
            $p8->descripcion = 'Orden de transporte';
            $p8->path_ruta = '/ot';
            $p8->usuarios_id = $user->id;
            $p8->save();

            $p9 = new permissions_detail;
            $p9->permission_id = 9;
            $p9->descripcion = 'Buscar por medidas';
            $p9->path_ruta = '/repuesto/buscar-medida';
            $p9->usuarios_id = $user->id;
            $p9->save();

            $p10 = new permissions_detail;
            $p10->permission_id = 6;
            $p10->descripcion = 'Clientes';
            $p10->path_ruta = '/clientes';
            $p10->usuarios_id = $user->id;
            $p10->save();

            $p11 = new permissions_detail;
            $p11->permission_id = 3;
            $p11->descripcion = 'Busqueda cliente';
            $p11->path_ruta = '/busqueda_cliente';
            $p11->usuarios_id = $user->id;
            $p11->save();

            $p12 = new permissions_detail;
            $p12->permission_id = 3;
            $p12->descripcion = 'Busqueda expres';
            $p12->path_ruta = '/busqueda_expres';
            $p12->usuarios_id = $user->id;
            $p12->save();

            $p13 = new permissions_detail;
            $p13->permission_id = 3;
            $p13->descripcion = 'Borrar carrito';
            $p13->path_ruta = '/borrar_carrito';
            $p13->usuarios_id = $user->id;
            $p13->save();

            $p14 = new permissions_detail;
            $p14->permission_id = 3;
            $p14->descripcion = 'Transferir carrito';
            $p14->path_ruta = '/transferir_carrito';
            $p14->usuarios_id = $user->id;
            $p14->save();

            $p15 = new permissions_detail;
            $p15->permission_id = 3;
            $p15->descripcion = 'Recuperar carrito';
            $p15->path_ruta = '/recuperar_carrito';
            $p15->usuarios_id = $user->id;
            $p15->save();
        }

        //Contabilidad
        if($request->role_id == 17){
            $p1 = new permissions_detail;
            $p1->permission_id = 5;
            $p1->descripcion = 'Cargar folios';
            $p1->path_ruta = '/sii/cargarfolios';
            $p1->usuarios_id = $user->id;
            $p1->save();

            $p2 = new permissions_detail;
            $p2->permission_id = 5;
            $p2->descripcion = 'Estado DTE';
            $p2->path_ruta = '/sii/estadodte';
            $p2->usuarios_id = $user->id;
            $p2->save();

            $p3 = new permissions_detail;
            $p3->permission_id = 7;
            $p3->descripcion = 'RCOF Boletas';
            $p3->path_ruta = '/rcof';
            $p3->usuarios_id = $user->id;
            $p3->save();

            $p4 = new permissions_detail;
            $p4->permission_id = 7;
            $p4->descripcion = 'Libro ventas';
            $p4->path_ruta = '/libro/ventas';
            $p4->usuarios_id = $user->id;
            $p4->save();
            
            $p5 = new permissions_detail;
            $p5->permission_id = 7;
            $p5->descripcion = 'Libro compras';
            $p5->path_ruta = '/libro/compras';
            $p5->usuarios_id = $user->id;
            $p5->save();

            $p6 = new permissions_detail;
            $p6->permission_id = 9;
            $p6->descripcion = 'Buscar por medidas';
            $p6->path_ruta = '/repuesto/buscar-medida';
            $p6->usuarios_id = $user->id;
            $p6->save();
        }

        //Estandar
        if($request->role_id == 19){
            $p1 = new permissions_detail;
            $p1->permission_id = 9;
            $p1->descripcion = 'Buscar por medidas';
            $p1->path_ruta = '/repuesto/buscar-medida';
            $p1->usuarios_id = $user->id;
            $p1->save();
        }

        $user->save();

        $info_user = $this->getUser($user->id);

        return [($user->rol),$info_user];
    }

    public function userUp($id){
        $user = User::find($id);
        $user->activo = 1;
        $user->save();
        return $user->name;
    }

    public function userDown($id){
        $user = User::find($id);
        $user->activo = 0;
        $user->save();
        return $user->name;
    }

    public function agregarPermisos(Request $request){
        $values = $request->input("permiso");
        $user_id = $request->input("user_id");
         
            for($i=0; $i < count($values); $i++){
                try {
                    $nuevoPermiso = new users_has_permissions;
                    $nuevoPermiso->user_id = intval($user_id);
                    $nuevoPermiso->permission_id = intval($values[$i]);
                    $nuevoPermiso->save();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                
            }
         

        return redirect('/usuarios');
    }

    public function guardarPermisosDetalles(Request $req){
        
        $permisos=$req->detalles;
        $usuario_id = $req->usuario_id;
        $permiso_id = $req->permisoId;
        try {
            
                foreach($permisos as $permiso){
                    $existe = $this->revisarSiExistePermiso($permiso,$usuario_id,$permiso_id);
                    if($existe > 0){
                        return "error";
                    }
                    $detalle_permiso = new permissions_detail;
                    $detalle_permiso->permission_id = $req->permisoId;
                    $detalle_permiso->descripcion = $permiso;
                    switch($permiso){
                        case 'Ventas':
                            $detalle_permiso->path_ruta = '/ventas';
                            break;
                        case 'Cotizaciones':
                            $detalle_permiso->path_ruta = '/cotizaciones';
                            break;
                        case 'Evaluar cotizaciones':
                            $detalle_permiso->path_ruta = '/ventas/cotizaciones';
                            break;
                        case 'Consignaciones':
                            $detalle_permiso->path_ruta = '/consignaciones';
                            break;
                        case 'Busqueda expres':
                            $detalle_permiso->path_ruta = '/busqueda_expres';
                            break;
                        case 'Busqueda cliente':
                            $detalle_permiso->path_ruta = '/busqueda_cliente';
                            break;
                        case 'Solo transferir carrito':
                            $detalle_permiso->path_ruta = '/solo_carritos_transferidos';
                            break;
                        case 'Borrar carrito':
                            $detalle_permiso->path_ruta = '/borrar_carrito';
                            break;
                        case 'Guardar carrito':
                            $detalle_permiso->path_ruta = '/guardar_carrito';
                            break;
                        case 'Recuperar carrito':
                            $detalle_permiso->path_ruta = '/recuperar_carrito';
                            break;
                        case 'Transferir carrito':
                            $detalle_permiso->path_ruta = '/transferir_carrito';
                            break;
                        case 'Nota de crédito':
                            $detalle_permiso->path_ruta = '/notacredito';
                            break;
                        case 'Nota de débito':
                            $detalle_permiso->path_ruta = '/notadebito';
                            break;
                        case 'Pedidos':
                            $detalle_permiso->path_ruta = '/ventas/pedidos_nuevo';
                            break;
                        case 'Arqueo de caja':
                            $detalle_permiso->path_ruta = '/ventas/arqueocaja';
                            break;
                        case 'Vales de mercadería':
                            $detalle_permiso->path_ruta = '/ventas/vale_por_mercaderia';
                            break;
                        case 'Vales de consignación':
                            $detalle_permiso->path_ruta = '/ventas/vale_consignacion';
                            break;   
                        case'Agregar referencia':
                            $detalle_permiso->path_ruta = '/agregar_referencia';
                            break;
                        case'Agregar repuesto expres':
                            $detalle_permiso->path_ruta = '/agregar_expres';
                            break;
                        case 'Facturas de compra':
                            $detalle_permiso->path_ruta = '/factuprodu/crear';
                            break;
                        case 'Listas facturas':
                            $detalle_permiso->path_ruta = '/compras/listar';
                            break;
                        case 'Buscar repuestos':
                            $detalle_permiso->path_ruta = '/repuesto';
                            break;
                        case 'Stock de repuestos':
                            $detalle_permiso->path_ruta = '/repuesto/stockrepuesto';
                            break;
                        case 'Ingresados vs vendidos':
                            $detalle_permiso->path_ruta = '/repuesto/ingresados_vendidos';
                            break;
                        case 'Guía de despacho':
                            $detalle_permiso->path_ruta = '/guiadespacho';
                            break;
                        case 'Recepción de guía despacho':
                            $detalle_permiso->path_ruta = '/';
                            break;
                        case 'Traspaso de mercadería':
                            $detalle_permiso->path_ruta = '/guiadespacho/traspaso_mercaderia';
                            break;
                        case 'Recepción de mercadería':
                            $detalle_permiso->path_ruta = '/guiadespacho/recepcion_mercaderia';
                            break;
                        case 'Devolución de mercadería':
                            $detalle_permiso->path_ruta = '/guiadespacho/devolucion_mercaderia';
                            break;
                        case 'Vista Ofertas':
                            $detalle_permiso->path_ruta = '/ventas/ofertas';
                            break;
                        case 'Orden de transporte':
                            $detalle_permiso->path_ruta = '/ot';
                            break;
                        case 'Inventario por tienda':
                            $detalle_permiso->path_ruta = '/inventario';
                            break;
                        case 'Solicitudes':
                            $detalle_permiso->path_ruta = '/solicitudes';
                            break;
                        case'Editar factura de ingreso':
                            $detalle_permiso->path_ruta = '/editar_factura';
                            break;
                        case'Mostrar ofertas web':
                            $detalle_permiso->path_ruta = '/ofertas_web';
                            break;
                        case'Ultimos repuestos':
                            $detalle_permiso->path_ruta = '/ultimos_repuestos';
                            break;
                        case 'Cargar folios':
                            $detalle_permiso->path_ruta = '/sii/cargarfolios';
                            break;
                        case 'Anulación de folios':
                            $detalle_permiso->path_ruta = '/sii/anularfolios';
                            break;
                        case 'Estado DTE':
                            $detalle_permiso->path_ruta = '/sii/estadodte';
                            break;
                        case 'Kit de repuestos':
                            $detalle_permiso->path_ruta = '/ventas/armar-kit';
                            break;
                        case 'Marca vehículo':
                            $detalle_permiso->path_ruta = '/marcavehiculo';
                            break;
                        case 'Modelo vehículo':
                            $detalle_permiso->path_ruta = '/modelovehiculo';
                            break;
                        case 'Roles de usuario':
                            $detalle_permiso->path_ruta = '/rol';
                            break;
                        case 'Familia de repuestos':
                            $detalle_permiso->path_ruta = '/familia';
                            break;
                        case 'Marca de repuestos':
                            $detalle_permiso->path_ruta = '/marcarepuesto';
                            break;
                        case 'Modificar repuestos':
                            $detalle_permiso->path_ruta = '/modificarRepuesto';
                            break;
                        case 'Catálogo de repuestos':
                            $detalle_permiso->path_ruta = '/repuesto';
                            break;
                        case 'Paises':
                            $detalle_permiso->path_ruta = '/pais';
                            break;
                        case 'Proveedores':
                            $detalle_permiso->path_ruta = '/proveedor';
                            break;
                        case 'Formas de pago':
                            $detalle_permiso->path_ruta = '/formapago';
                            break;
                        case 'Límites de créditos':
                            $detalle_permiso->path_ruta = '/limitecredito';
                            break;
                        case 'Días de crédito':
                            $detalle_permiso->path_ruta = '/diascredito';
                            break;
                        case 'Clientes':
                            $detalle_permiso->path_ruta = '/clientes';
                            break;
                        case 'Estado de cuenta cliente':
                            $detalle_permiso->path_ruta = '/clientes/estado';
                            break;
                        case 'Cliente express':
                            $detalle_permiso->path_ruta = '/clientes/express';
                            break;
                        case 'Parametros':
                            $detalle_permiso->path_ruta = '/parametros';
                            break;
                        case 'Repuestos relacionados':
                            $detalle_permiso->path_ruta = '/relacionados';
                            break;
                        case 'RCOF Boletas':
                            $detalle_permiso->path_ruta = '/rcof';
                            break;
                        case 'Libro ventas':
                            $detalle_permiso->path_ruta = '/libro/ventas';
                            break;
                        case 'Libro compras':
                            $detalle_permiso->path_ruta = '/libro/compras';
                            break;
                        case 'Ventas diarias':
                            $detalle_permiso->path_ruta = '/reportes/ventasdiarias';
                            break;
                        case 'Detalle ventas':
                            $detalle_permiso->path_ruta = '/ventas/detalle_ventas';
                            break;
                        case 'Desempeño mensual':
                            $detalle_permiso->path_ruta = '/usuarios/rendimiento';
                            break;
                        case 'Documentos generados':
                            $detalle_permiso->path_ruta = '/reportes/documentosgenerados';
                            break;
                        case 'Buscar documentos':
                            $detalle_permiso->path_ruta = '/reportes/documentosgenera2';
                            break;
                        case 'Documentos Transbank':
                            $detalle_permiso->path_ruta = '/reportes/transbank';
                            break;
                        case 'Documentos Getnet':
                            $detalle_permiso->path_ruta = '/reportes/getnet';
                            break;
                        case 'Buscar por medidas':
                            $detalle_permiso->path_ruta = '/repuesto/buscar-medida';
                            break;
                        case 'Modificar precio':
                            $detalle_permiso->path_ruta = '/repuesto/modificar-precio';
                            break;
                        case 'Actualizar precio':
                            $detalle_permiso->path_ruta = '/actualizar-precio';
                            break;
                        default:
                            $detalle_permiso->path_ruta = '/';
                            
                    }
                    $detalle_permiso->usuarios_id = $usuario_id;
                    $detalle_permiso->save();
            }
    
            $info_user = $this->getUser($usuario_id);
            
            return $info_user;
            
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        
    }

    public function revisarSiExistePermiso($descripcionPermiso,$usuario_id, $permiso_id){
        $permiso = permissions_detail::where('descripcion',$descripcionPermiso)->where('usuarios_id',$usuario_id)->where('permission_id',$permiso_id)->get();
        return $permiso->count();
    }

    public function quitarPermisos(Request $request){
        $id = intval($request->id);
        $user_id = intval($request->user_id);
        
        try {
            $permiso_eliminar = permissions_detail::where('usuarios_id', $user_id)
                ->where('id', $id)
                ->delete();
                
            $nuevos_permisos =  $this->dame_permisos($user_id);
            $info_user = $this->getUser($user_id);
            
            return $info_user;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        


    }

    function dame_permisos($user_id){
        $permisos_del_usuario = permissions_detail::where('usuarios_id',$user_id)->get();
        return $permisos_del_usuario;
    }

    function rendimiento_vista(){
        $permisos_detalles = permissions_detail::all();
        foreach($permisos_detalles as $permiso_detalle){
            if($permiso_detalle->permission_id == 8 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/usuarios/rendimiento'){
                return view('reportes.rendimiento');
                }
        }
        $user = Auth::user();
        if ($user->rol->nombrerol =="Administrador") {
            return view('reportes.rendimiento');
        } else {
            return redirect('home');
        }
        
    }

    public function usuario_servidor_vista(){
        try {
            //Sacamos al usuario FROJO
            $usuarios_servidor = user_server::where('id','<>',16)->where('activo',1)->get();
            foreach($usuarios_servidor as $u){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = Carbon::parse($u->fecha_actualizacion_password)->format("d-m-Y");
                $secondDate = date('d-m-Y');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $u->dias = $dias;
            }
            
            return view('manten.usuarios_servidor',['usuarios'=>$usuarios_servidor]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function usuario_servidor($id){
        try {
            $usuario_servidor = user_server::select('user_server.user','user_server.created_at','user_server.activo','user_server.fecha_actualizacion_password','registro_login.direccion_ip','registro_login.fecha_login','user_server.id','registro_login.fecha_ingreso')
                                    ->leftjoin('registro_login','user_server.id','registro_login.usuario_id_servidor')
                                    ->where('user_server.id',$id)
                                    ->orderBy('registro_login.id','desc')
                                    ->get();
            
                //Separamos el valor de updated_at para que solo me muestre la fecha en formato YYYY-MM-DD que será guardado en la primera posición
                $porciones = explode("T", $usuario_servidor[0]->created_at);
                //Creamos un nuevo atributo al objeto u que contendrá la fecha formateada correctamente.
                $usuario_servidor[0]->fecha = Carbon::parse($porciones[0])->format("d-m-Y");
                $usuario_servidor[0]->hora = date('H:i',strtotime($usuario_servidor[0]->fecha_login));
                $usuario_servidor[0]->fecha_ingreso = Carbon::parse($usuario_servidor[0]->fecha_ingreso)->format("d-m-Y");
            return $usuario_servidor[0];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function agregar_usuario_servidor(Request $request){
        
        $name = $request->input('user');
        $password = $request->input('passworduser');
        
        $password_encrypt = Hash::make($password);
        
        if($name == '' || $password == ''){
            return redirect('/usuarios/servidor')->with('error','Error en el ingreso de la información');
        }
        
        try {
            $user_server = new user_server;
            
            $user_server->user = $name;
            
            $user_server->password = $password_encrypt;
            $user_server->fecha_actualizacion_password = Carbon::today()->toDateString();
            $user_server->activo = 1;
            $user_server->save();
            return redirect('/usuarios/servidor')->with('msg','Usuario creado con éxito');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    function editar_usuario_servidor($usuario_id){
        $usuario = user_server::find($usuario_id);
        return $usuario;
    }

    function editar_usuario_servidor_post(Request $request){
        try {
            $usuario_servidor_id = $request->input('usuario_servidor_id');
            
        $user = $request->input('user');
        $password = $request->input('passworduser');
        $password_confirm = $request->input('password_confirm');

        if(is_null($password)){
            return redirect('/usuarios/servidor')->with('error','No se pudo editar el usuario, no ingreso el password');
        }

        if(is_null($password_confirm)){
            return redirect('/usuarios/servidor')->with('error','No se pudo editar el usuario, debe ingresar la confirmación del password');
        }

        if($password !== $password_confirm){
            return redirect('/usuarios/servidor')->with('error','No se pudo editar el usuario, no coinciden los password');
        }

        $password_encrypt = Hash::make($password);

        $usuario_servidor = user_server::where('user',$user)->first();
       
        $usuario_servidor->user = $user;
        $usuario_servidor->password = $password_encrypt;
        $usuario_servidor->fecha_actualizacion_password = Carbon::today()->toDateString();
        $usuario_servidor->save();
        return redirect('/usuarios/servidor')->with('msg','Usuario editado con éxito');
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        

    }

    function eliminar_usuario_servidor($usuario_id){
        
        try {
            $usuario = user_server::where('id',$usuario_id)->first();
            
            $usuario->activo = 2;
            $usuario->update();

            $usuarios = user_server::where('activo',1)->where('id','<>',16)->get();
            foreach($usuarios as $u){
                //Fecha ultima actualización del precio del repuesto
                $firstDate = Carbon::parse($u->fecha_actualizacion_password)->format("d-m-Y");
                $secondDate = date('d-m-Y');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
                $u->dias = $dias;
            }

            return $usuarios;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
        
    }
}
