<!--Si existen permisos es un usuario con permisos especiales -->

<script type="text/javascript">
  function logout(event){
    // event.preventDefault();
    let user_server = window.localStorage.getItem('user');
    alert(user_server);
  }
</script>

@if(count(Auth::user()->permisos) > 0)
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navHeader" >
  <a href="/home"><img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" srcset="" class="logoHeader"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      @if(Auth::user()->dame_permisos_venta()->count() > 0)
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ventas</a>
          
          <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            {{-- <a class="dropdown-item" href="/ventas">Ventas (Fact-Bole)</a>
            <a class="dropdown-item" href="/notacredito">Nota de Crédito</a> --}}
            @foreach(Auth::user()->dame_permisos_venta() as $permiso)
              @if($permiso->path_ruta !== '/agregar_expres' && $permiso->path_ruta !== '/agregar_referencia' && $permiso->path_ruta !== '/solo_carritos_transferidos' && $permiso->path_ruta !== '/cotizaciones' && $permiso->path_ruta !== '/consignaciones' && $permiso->path_ruta !== '/busqueda_expres' && $permiso->path_ruta !== '/busqueda_cliente' && $permiso->path_ruta !== '/borrar_carrito' && $permiso->path_ruta !== '/guardar_carrito' && $permiso->path_ruta !== '/recuperar_carrito' && $permiso->path_ruta !== '/transferir_carrito')<a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> @endif
            @endforeach
          </div>
        </li>
      @endif
      @if(Auth::user()->dame_permisos_inventario()->count() > 0)
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  {{-- <a class="dropdown-item" href="/factuprodu/crear">Facturas de Compra</a>
                  <a class="dropdown-item" href="/compras/listar">Listar Facturas (Compras)</a>
                  <a class="dropdown-item" href="/compras/listar">Orden de transporte</a> --}}
                  @foreach(Auth::user()->dame_permisos_inventario() as $permiso)
                    @if($permiso->path_ruta !== '/editar_factura' && $permiso->path_ruta !== '/ofertas_web' && $permiso->path_ruta !== '/ultimos_repuestos' && $permiso->path_ruta !== '/ventas/cotizaciones')<a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a>  @endif
                  @endforeach
            </div>
          </li>
        
      @endif
    @if(Auth::user()->dame_permisos_sii()->count() > 0)
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">SII</a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            @foreach(Auth::user()->dame_permisos_sii() as $permiso)
            <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> 
            @endforeach
          </div>
        </li>
    @endif   
    @if(Auth::user()->dame_permisos_libros()->count() > 0)
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownLibroLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Libros</a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownLibroLink">
            @foreach(Auth::user()->dame_permisos_libros() as $permiso)
            <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> 
            @endforeach
          </div>
        </li>
    @endif
    @if(Auth::user()->dame_permisos_mantenimiento()->count() > 0)
    @php $opt = true; @endphp
    @foreach(Auth::user()->dame_permisos_mantenimiento() as $permiso)
    @if($permiso->path_ruta !== '/modificarRepuesto')
      @php $opt = false; @endphp
    @endif
    @endforeach
    @if($opt == false)
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownLibroLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Mantenimiento</a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownLibroLink">
            @foreach(Auth::user()->dame_permisos_mantenimiento() as $permiso)
            @if($permiso->path_ruta !== '/modificarRepuesto')<a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> @endif
            @endforeach
          </div>
        </li>
        @endif
    @endif   
    @if(Auth::user()->dame_permisos_reportes()->count() > 0)
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownReportesLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Reportes</a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownReportesLink">
            @foreach(Auth::user()->dame_permisos_reportes() as $permiso)
            <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> 
            @endforeach
          </div>
        </li>
    @endif
     
      <li class="nav-item dropdown">
        <a href="" class="nav-link dropdown-toggle" id="navbarDroprdownPruebaLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Repuestos</a>
        <div class="dropdown-menu" aria-labelledby="navbarDroprdownPruebaLink">
          <a href="/repuesto/buscar" class="dropdown-item">Busqueda</a>
          @if(Auth::user()->dame_permisos_busqueda_repuesto()->count() > 0)  
            @foreach(Auth::user()->dame_permisos_busqueda_repuesto() as $permiso)
            @if($permiso->path_ruta !== '/repuesto/modificar-precio' && $permiso->path_ruta !== '/actualizar-precio')<a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> @endif
            @endforeach
          @endif
        </div>
      </li>
    
    </ul>
    @if(Auth::user()->image_path)
    @if(Session::get('ofertas') == 'SI') <a href="javascript:void(0)" onclick="dameofertas()" ><i class="fa-solid fa-bell mr-2 text-danger" style="font-size: 14px;">Ofertas y Descuentos</i></a> @endif
        <img src="{{url('usuarios/avatar/'.Auth::user()->image_path)}}" alt="" id="logo">
      
      @endif
      @if(Auth::user()->rol->nombrerol !== "Bodeguer@")
      <div class="d-none text-white letra_pequeña" id="porcentaje_ventas"></div>
      @endif
    <ul class="navbar-nav">
        
      <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuUser" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{Auth::user() -> name}} ({{Auth::user()->rol->nombrerol}})</a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuUser">
            {{-- <a href="{{url('usuarios/edit/'.Auth::user()->id)}}" class="dropdown-item">Editar usuario</a> --}}
            <a href="/cambiarclave" class="dropdown-item">Cambiar clave</a>
            <form action="/logout" method="post">
              @csrf
                <button class="dropdown-item" type="submit" style="color: white;">Salir</button>
              </form>
            
          </div>
      </li>
      </ul>
    </div>
    
</nav>
<!--Sino es un usuario con un rol definido -->
@else
  <nav class="navbar navbar-expand-lg navbar-light bg-light" id="navHeader" >
    <a href="/home"><img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" srcset="" class="logoHeader"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" style="border-color: white;">
      <span class="navbar-toggler-icon"></span>
    </button>
  
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="background: black; z-index: 100;">
      <ul class="navbar-nav mr-auto" style="font-size: 13px;">
       @if (Auth::user()->rol->nombrerol !== "contabilidad" && Auth::user()->rol->nombrerol !== "jefe de bodega")
       <li class="nav-item dropdown">
        @if(Auth::user()->rol->nombrerol !== "Bodeguer@") 
        <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ventas</a> @endif
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="/ventas">Ventas (Fact-Bole)</a>
          @if(Auth::user()->rol->nombrerol === "Cajer@" || Auth::user()->rol->nombrerol === "Administrador")
          <a class="dropdown-item" href="/notacredito">Nota de Crédito</a>
          <a class="dropdown-item" href="/notadebito">Nota de Débito</a>
          <a class="dropdown-item" href="/ventas/arqueocaja">Arqueo de caja</a>
          <a class="dropdown-item" href="/ventas/pedidos_nuevo">Pedidos</a>
          <a class="dropdown-item" href="/ventas/vale_por_mercaderia">Vale por mercadería</a>
          @endif
          @if(Auth::user()->id === 5 || Auth::user()->id == 16)
          <a class="dropdown-item" href="/ventas/metas">Metas</a>
          @endif
        </div>
    </li>  
       @endif
        
      @if(Auth::user()->rol->nombrerol == "Encargado de RRHH")
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Recursos humanos</a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="#">Dias trabajados</a>
            <a class="dropdown-item" href="#">Datos del trabajador</a>
          </div>
        </li>
      @endif
      
          @if (Auth::user()->rol->nombrerol == "Administrador" || Auth::user()->rol->nombrerol == "Bodeguer@" || Auth::user()->rol->nombrerol == "bodega-venta" || Auth::user()->rol->nombrerol == "jefe de bodega" )
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="/factuprodu/crear">Facturas de Compra</a>
            <a class="dropdown-item" href="/compras/listar">Listar Facturas (Compras)</a>
            <a class="dropdown-item" href="/compras/listar_por_factura">Buscar Factura (Compras)</a>
            @if(Auth::user()->rol->nombrerol == "Administrador") 
              <a href="/repuesto" class="dropdown-item">Buscar repuesto</a>
              <a class="dropdown-item" href="/repuesto/stockrepuesto">Stock de Repuestos</a>
              <a href="/repuesto/ingresados_vendidos" class="dropdown-item">Ingresados vs Vendidos</a>
              <a href="/ventas/ofertas" class="dropdown-item">Ofertas y Descuentos</a>
              <a href="/ventas/estadisticas" class="dropdown-item">Estadisticas</a>
              <a href="/ticket" class="dropdown-item">Ticket</a>
              <a href="/ventas/armar-kit" class="dropdown-item">Kit de repuestos</a>
              <a href="/ventas/cotizaciones" class="dropdown-item">Evaluar cotizaciones</a>
            @endif
            <a href="/guiadespacho" class="dropdown-item">Guía de despacho</a>
            
            <a href="/guiadespacho/traspaso_mercaderia" class="dropdown-item">Traspaso de mercadería</a>
            <a href="/guiadespacho/recepcion_mercaderia" class="dropdown-item">Recepción de mercadería</a>
            <a href="/guiadespacho/devolucion_mercaderia" class="dropdown-item">Devoluciones</a>
            <a class="dropdown-item" href="/ot">Orden de transporte</a>
            @if(Auth::user()->rol->nombrerol == "Administrador")
              <a class="dropdown-item" href="/inventario">Inventario por tienda</a>
            @endif
            @if(Auth::user()->rol->nombrerol == "jefe de bodega" ||Auth::user()->rol->nombrerol == "Administrador" ) 
              <a href="/solicitudes" class="dropdown-item">Solicitudes</a> 
            @endif
          </div>
          </li>
              
          @endif

          @if(Auth::user()->name == "Marveise Albarracin")
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
              <a href="/guiadespacho/recepcion_mercaderia" class="dropdown-item">Recepción de mercadería</a>
            </div>
          </li>
          @endif
          
          @if (Auth::user()->rol->nombrerol == "Administrador" || Auth::user()->rol->nombrerol == "contabilidad")
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">SII</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="/sii/cargarfolios">Cargar Folios</a>
            <a class="dropdown-item" href="/sii/anularfolios">Anulación de folios</a>
            <a class="dropdown-item" href="/sii/estadodte">Estado DTE</a>
            {{-- <a class="dropdown-item" href="/sii/verestado">Estado de Envíos</a> --}}
            <a class="dropdown-item" href="/sii/ambiente">Ambiente Certificación</a>
            </div>
        </li>
          @endif

         
          
          @if(Auth::user()->rol->nombrerol == "Administrador")
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  <a class="dropdown-item" href="/marcavehiculo">Marca Vehículos</a>
                  <a class="dropdown-item" href="/modelovehiculo">Modelo Vehículos</a>
                  <a class="dropdown-item" href="/rol">Roles de Usuario</a>
                  <a class="dropdown-item" href="/familia">Familia de Repuestos</a>
                  <a class="dropdown-item" href="/marcarepuesto">Marca de Repuestos</a>
                  <a class="dropdown-item" href="/repuesto/listar-oems">Clonar OEMs</a>
                  <a href="/repuesto/clonaciones" class="dropdown-item">Clonaciones</a>
                  {{-- <a class="dropdown-item" href="/repuesto/modificar">Modificar Repuestos</a> --}}
                  <a class="dropdown-item" href="/repuesto">Catálogo de Repuestos</a>
                  <a class="dropdown-item" href="/pais">Países</a>
                  <a class="dropdown-item" href="/proveedor">Proveedores</a>
                  <a class="dropdown-item" href="/formapago">Formas de Pago</a>
                  <a class="dropdown-item" href="/limitecredito">Límites de Crédito</a>
                  <a class="dropdown-item" href="/diascredito">Días de Crédito</a>
                  <a class="dropdown-item" href="/clientes">Agregar Clientes</a>
                  <a class="dropdown-item" href="/clientes/estado">Estado de cuenta cliente</a>
                  <a class="dropdown-item" href="/clientes/xpress">Clientes Xpress</a>
                  <a class="dropdown-item" href="/repuesto/xpress">Repuestos Xpress</a>
                  <a class="dropdown-item" href="/repuesto/inactivos">Repuestos Inactivos</a>
                  <a class="dropdown-item" href="/parametros">Parámetros</a>
                  <a class="dropdown-item" href="/relacionados">Repuestos Relacionados</a>
                  <div class="dropdown-divider"></div>
                  <div class="dropdown-header">Sistema</div>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="/usuarios">Usuarios</a>
                  <a class="dropdown-item" href="/usuarios/servidor">Servidor</a>
              </div>
          </li>
          @endif
          @if(Auth::user()->rol->nombrerol == "bodega-venta" || Auth::user()->rol->nombrerol == "vendedor" || Auth::user()->rol->nombrerol == "Cajer@")
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  
                  <a class="dropdown-item" href="/clientes">Agregar Clientes</a>
                  
                  
              </div>
          </li>
          @endif
          @if(Auth::user()->rol->nombrerol == "Administrador" || Auth::user()->rol->nombrerol == "contabilidad")
          <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownLibroLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Libros</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownLibroLink">
              <a class="dropdown-item" href="/rcof/">RCOF Boletas</a>
              <a class="dropdown-item" href="/libro/ventas">Libro Ventas</a>
              <a class="dropdown-item" href="/libro/compras">Libro Compras</a>
            </div>
          </li>
          @endif
          @if(Auth::user()->rol->nombrerol == "Administrador")
          <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownReportesLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Reportes</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownReportesLink">
              <a class="dropdown-item" href="/reportes/ventasdiarias">Ventas diarias</a>
              <a class="dropdown-item" href="/usuarios/rendimiento">Desempeño mensual</a>
              <a class="dropdown-item" href="/reportes/documentosgenerados">Documentos generados</a>
              <a class="dropdown-item" href="/reportes/documentosgenera2">Buscar documentos</a>
              <a class="dropdown-item" href="/reportes/transbank">Documentos Banco Estado</a>
              <a class="dropdown-item" href="/reportes/getnet">Documentos Getnet</a>
              <a class="dropdown-item" href="/ventas/ventas_online">Ventas Online</a>
            </div>
          </li>
          @endif

          <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownBusquedaNueva" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Repuestos</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownBusquedaNueva">
              <a class="dropdown-item" href="/repuesto/buscar">Buscar repuesto</a>
              <a class="dropdown-item" href="/repuesto/buscar-medida">Buscar por medidas</a>
              
            </div>
          </li>
          <li class="nav-item">
            <a class="dropdown-item" href="/ventas/detalle_ventas">Detalle Ventas</a>
          </li>
      </ul>
      @if(Auth::user()->image_path)
      @if(Auth::user()->rol->nombrerol == "Administrador")
      <a href="/reportes/rep_sin_prov" class="d-flex align-items-center mx-3 text-decoration-none" style="font-size: 13px;">
        <img src="{{asset('storage/imagenes/campana.png')}}" alt="" style="width: 30px;">Sin Stock Prov <span class="d-none badge badge-warning" id="cantidad_sin_prov"></span>
      </a>
      <a href="/reportes/bajo_stock" class="d-flex align-items-center mx-3 text-decoration-none" style="font-size: 13px;">
        <img src="{{asset('storage/imagenes/campana.png')}}" alt="" style="width: 30px;">Stock Mínimo <span class="d-none badge badge-warning" id="cantidad_stock_minimo"></span>
      </a> 
      
      <a href="javascript:void(0)" onclick="dameofertas()" class="d-flex mx-3 align-items-center text-decoration-none" style="font-size: 13px;" >
        <img src="{{asset('storage/imagenes/campana.png')}}" alt="" style="width: 30px;">Ofertas
      </a>
      @endif
        <img src="{{url('usuarios/avatar/'.Auth::user()->image_path)}}" alt="" id="logo">
      
      @endif
      @if(Auth::user()->rol->nombrerol !== "Bodeguer@")
      <div class="d-none text-white letra_pequeña" id="porcentaje_ventas"></div>
      @endif
      <ul class="navbar-nav">
        
      <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdownMenuUser" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuUser">
            <span class="text-center">{{Auth::user() -> name}}  ({{Auth::user()->rol->nombrerol}})</span> 
            @if(Auth::user()->rol->nombrerol === "Administrador") <a href="{{url('usuarios/edit/'.Auth::user()->id)}}" class="dropdown-item">Editar usuario</a> @endif
            <a href="/cambiarclave" class="dropdown-item">Cambiar clave</a>
            <form action="/logout" method="post">
            @csrf
              <button class="dropdown-item" type="submit" style="color: white;">Salir</button>
            </form>
            
          </div>
      </li>
      </ul>
      
    </div>
    
  </nav>
  
  

@endif

