@php
  $usuarioCabecera = Auth::user();
  $rolCabecera = $usuarioCabecera->rol->nombrerol;
  $permisosVenta = $usuarioCabecera->dame_permisos_venta();
  $permisosInventario = $usuarioCabecera->dame_permisos_inventario();
  $permisosSii = $usuarioCabecera->dame_permisos_sii();
  $permisosLibros = $usuarioCabecera->dame_permisos_libros();
  $permisosMantenimiento = $usuarioCabecera->dame_permisos_mantenimiento();
  $permisosReportes = $usuarioCabecera->dame_permisos_reportes();
  $permisosBusquedaRepuesto = $usuarioCabecera->dame_permisos_busqueda_repuesto();
  $tienePermisosEspeciales = $usuarioCabecera->permisos->isNotEmpty();
  $notificacionesCabecera = [
    '/reportes/stock_critico' => 'cantidad_stock_critico',
    '/reportes/bajo_stock' => 'cantidad_stock_minimo',
    '/reportes/rep_sin_prov' => 'cantidad_sin_prov',
    '/reportes/stock_pendiente' => 'cantidad_stock_pendiente',
  ];
  $rutasOcultasVenta = [
    '/agregar_expres', '/agregar_referencia', '/solo_carritos_transferidos',
    '/cotizaciones', '/consignaciones', '/busqueda_expres', '/busqueda_cliente',
    '/borrar_carrito', '/guardar_carrito', '/recuperar_carrito', '/transferir_carrito',
  ];
  $rutasOcultasInventario = [
    '/editar_factura', '/reportes/bajo_stock', '/reportes/rep_sin_prov',
    '/reportes/stock_critico', '/ofertas_web', '/ultimos_repuestos', '/ventas/cotizaciones',
  ];
  $rutasOcultasRepuestos = [
    '/repuesto/modificar-precio', '/actualizar-precio', '/reportes/ultimas-compras',
  ];
@endphp

{{-- Los permisos se cargan una sola vez para evitar consultas repetidas desde Blade. --}}
@if($tienePermisosEspeciales)
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navHeader">
  <a href="/home"><img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" srcset="" class="logoHeader"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarPermisosContent" aria-controls="navbarPermisosContent" aria-expanded="false" aria-label="Abrir navegación">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarPermisosContent">
    <ul class="navbar-nav mr-auto">
      @if($permisosVenta->isNotEmpty())
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuVentasPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ventas</a>

          <div class="dropdown-menu" aria-labelledby="menuVentasPermisos">
            {{-- <a class="dropdown-item" href="/ventas">Ventas (Fact-Bole)</a>
            <a class="dropdown-item" href="/notacredito">Nota de Crédito</a> --}}
            @foreach($permisosVenta as $permiso)
              @if(!in_array($permiso->path_ruta, $rutasOcultasVenta, true))<a class="dropdown-item" href="{{ $permiso->path_ruta }}">{{ $permiso->descripcion }}</a> @endif
            @endforeach
          </div>
        </li>
      @endif
       @if($permisosInventario->isNotEmpty())
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuInventariosPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="menuInventariosPermisos">
                  {{-- <a class="dropdown-item" href="/factuprodu/crear">Facturas de Compra</a>
                  <a class="dropdown-item" href="/compras/listar">Listar Facturas (Compras)</a>
                  <a class="dropdown-item" href="/compras/listar">Orden de transporte</a> --}}
                   @foreach($permisosInventario as $permiso)
                    @if(!in_array($permiso->path_ruta, $rutasOcultasInventario, true))<a class="dropdown-item" href="{{ $permiso->path_ruta }}">{{ $permiso->descripcion }}</a> @endif
                  @endforeach
            </div>
          </li>
        
      @endif
    @if($permisosSii->isNotEmpty())
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuSiiPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">SII</a>
          <div class="dropdown-menu" aria-labelledby="menuSiiPermisos">
            @foreach($permisosSii as $permiso)
            @if($permiso->path_ruta !== '/sii/ultimosestadodte') <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> @endif
            @endforeach
          </div>
        </li>
    @endif   
    @if($permisosLibros->isNotEmpty())
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="menuLibrosPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Libros</a>
          <div class="dropdown-menu" aria-labelledby="menuLibrosPermisos">
            @foreach($permisosLibros as $permiso)
            <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> 
            @endforeach
          </div>
        </li>
    @endif
    @if($permisosMantenimiento->isNotEmpty())
    @php $opt = true; @endphp
    @foreach($permisosMantenimiento as $permiso)
    @if($permiso->path_ruta !== '/modificarRepuesto')
      @php $opt = false; @endphp
    @endif
    @endforeach
    @if($opt == false)
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="menuMantenimientoPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
          <div class="dropdown-menu" aria-labelledby="menuMantenimientoPermisos">
            @foreach($permisosMantenimiento as $permiso)
            @if($permiso->path_ruta !== '/modificarRepuesto')<a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> @endif
            @endforeach
          </div>
        </li>
        @endif
    @endif   
    @if($permisosReportes->isNotEmpty())
        <li class="nav-item dropdown">
          <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="menuReportesPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Reportes</a>
          <div class="dropdown-menu" aria-labelledby="menuReportesPermisos">
            @foreach($permisosReportes as $permiso)
            <a class="dropdown-item" href="{{$permiso->path_ruta}}">{{$permiso->descripcion}}</a> 
            @endforeach
          </div>
        </li>
    @endif
     
      <li class="nav-item dropdown">
        <a href="" class="nav-link dropdown-toggle" id="navbarDroprdownPruebaLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Repuestos</a>
        <div class="dropdown-menu" aria-labelledby="navbarDroprdownPruebaLink">
          <a href="/repuesto/buscar" class="dropdown-item">Busqueda</a>
          @if($permisosBusquedaRepuesto->isNotEmpty())
            @foreach($permisosBusquedaRepuesto as $permiso)
            @if(!in_array($permiso->path_ruta, $rutasOcultasRepuestos, true))<a class="dropdown-item" href="{{ $permiso->path_ruta }}">{{ $permiso->descripcion }}</a> @endif
            @endforeach
          @endif
        </div>
      </li>
    
    </ul>

@if(
    $permisosInventario
        ->whereIn('path_ruta', array_keys($notificacionesCabecera))
        ->count() > 0
)

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center"
       href="javascript:void(0)"
       id="menuNotificacionesPermisos"
       data-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">

        <img src="{{ asset('storage/imagenes/campana.png') }}"
             alt=""
             style="width: 25px;" class="mr-1">

        Notificaciones
    </a>

    <div class="dropdown-menu dropdown-menu-right"
         aria-labelledby="menuNotificacionesPermisos">

        @foreach($permisosInventario as $permiso)

            @if(isset($notificacionesCabecera[$permiso->path_ruta]))

                <a href="{{ $permiso->path_ruta }}"
                   class="dropdown-item d-flex justify-content-between align-items-center">

                    {{ $permiso->descripcion }}

                    <span class="d-none badge badge-warning"
                          id="{{ $notificacionesCabecera[$permiso->path_ruta] }}">
                    </span>

                </a>

            @endif

        @endforeach

    </div>
</li>

@endif

    @if(Session::get('ofertas') == 'SI')
    <button type="button" class="btn btn-link nav-link" onclick="dameofertas()"><i class="fa-solid fa-bell mr-1" aria-hidden="true"></i>Ofertas</button>
    @endif

      @if($rolCabecera !== "Bodeguer@")
      <div class="d-none text-white letra_pequeña" id="porcentaje_ventas"></div>
      @endif
      <ul class="navbar-nav">
        
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="javascript:void(0)" id="menuUsuarioPermisos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de usuario">
          @if($usuarioCabecera->image_path)
            <img src="{{ url('usuarios/avatar/'.$usuarioCabecera->image_path) }}" alt="Foto de {{ $usuarioCabecera->name }}" id="logo">
          @else
            <i class="fas fa-user-circle fa-2x" aria-hidden="true"></i>
          @endif
        </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="menuUsuarioPermisos">
              <span class="dropdown-item-text text-center">{{ $usuarioCabecera->name }} ({{ $rolCabecera }})</span>
              @if($rolCabecera === "Administrador") <a href="{{url('usuarios/edit/'.$usuarioCabecera->id)}}" class="dropdown-item">Editar usuario</a> @endif
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
  <nav class="navbar navbar-expand-lg navbar-light bg-light" id="navHeader">
    <a href="/home"><img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="" srcset="" class="logoHeader"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarRolesContent" aria-controls="navbarRolesContent" aria-expanded="false" aria-label="Abrir navegación" style="border-color: white;">
      <span class="navbar-toggler-icon"></span>
    </button>
  
    <div class="collapse navbar-collapse" id="navbarRolesContent" style="background: black; z-index: 100;">
      <ul class="navbar-nav mr-auto" style="font-size: 13px;">
       @if(!in_array($rolCabecera, ["contabilidad", "jefe de bodega"], true))
       <li class="nav-item dropdown">
        @if($rolCabecera !== "Bodeguer@")
         <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuVentasRol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ventas</a> @endif
         <div class="dropdown-menu" aria-labelledby="menuVentasRol">
          <a class="dropdown-item" href="/ventas">Ventas (Fact-Bole)</a>
          @if(in_array($rolCabecera, ["Cajer@", "Administrador"], true))
          <a class="dropdown-item" href="/notacredito">Nota de Crédito</a>
          <a class="dropdown-item" href="/notadebito">Nota de Débito</a>
          <a class="dropdown-item" href="/ventas/arqueocaja">Arqueo de caja</a>
          <a class="dropdown-item" href="/ventas/pedidos_nuevo">Pedidos</a>
          <a class="dropdown-item" href="/ventas/vale_por_mercaderia">Vale por mercadería</a>
          @endif
          @if(in_array($usuarioCabecera->id, [5, 16], true))
          <a class="dropdown-item" href="/ventas/metas">Metas</a>
          @endif
        </div>
    </li>  
       @endif
        
      @if($rolCabecera === "Encargado de RRHH")
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuRecursosHumanos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Recursos humanos</a>
          <div class="dropdown-menu" aria-labelledby="menuRecursosHumanos">
            <a class="dropdown-item" href="#">Dias trabajados</a>
            <a class="dropdown-item" href="#">Datos del trabajador</a>
          </div>
        </li>
      @endif
      
          @if(in_array($rolCabecera, ["Administrador", "Bodeguer@", "bodega-venta", "jefe de bodega"], true))
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuInventariosRol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="menuInventariosRol">
            <a class="dropdown-item" href="/factuprodu/crear">Facturas de Compra sin XML</a>
            <a class="dropdown-item" href="/factuprodu/crear_importacion">Facturas de Compra con XML</a>
            <a class="dropdown-item" href="/compras/listar">Listar Facturas (Compras)</a>
            <a class="dropdown-item" href="/compras/listar_por_factura">Buscar Factura (Compras)</a>
            @if($rolCabecera === "Administrador")
              <a href="/repuesto" class="dropdown-item">Buscar repuesto</a>
              <a class="dropdown-item" href="/repuesto/stockrepuesto">Stock de Repuestos</a>
              <a href="/repuesto/ingresados_vendidos" class="dropdown-item">Ingresados vs Vendidos</a>
              <a href="/ventas/ofertas" class="dropdown-item">Ofertas y Descuentos</a>
              <a href="/ventas/estadisticas" class="dropdown-item">Estadisticas</a>
              <a href="/ticket" class="dropdown-item">Ticket</a>
              <a href="/ventas/armar-kit" class="dropdown-item">Kit de repuestos</a>
              <a href="/ventas/cotizaciones" class="dropdown-item">Evaluar cotizaciones</a>
              <a href="/ventas/totem" class="dropdown-item">Revisión Carritos</a>
            @endif
            <a href="/guiadespacho" class="dropdown-item">Guía de despacho</a>
            
            <a href="/guiadespacho/traspaso_mercaderia" class="dropdown-item">Traspaso de mercadería</a>
            <a href="/guiadespacho/recepcion_mercaderia" class="dropdown-item">Recepción de mercadería</a>
            <a href="/guiadespacho/devolucion_mercaderia" class="dropdown-item">Devoluciones</a>
            <a class="dropdown-item" href="/ot">Orden de transporte</a>
            @if($rolCabecera === "Administrador")
              <a class="dropdown-item" href="/inventario">Inventario por tienda</a>
            @endif
            @if(in_array($rolCabecera, ["jefe de bodega", "Administrador"], true))
              <a href="/solicitudes" class="dropdown-item">Solicitudes</a> 
            @endif
          </div>
          </li>
              
          @endif

          @if($usuarioCabecera->name === "Marveise Albarracin")
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuInventarioRecepcionEspecial" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Inventarios</a>
            <div class="dropdown-menu" aria-labelledby="menuInventarioRecepcionEspecial">
              <a href="/guiadespacho/recepcion_mercaderia" class="dropdown-item">Recepción de mercadería</a>
            </div>
          </li>
          @endif
          
          @if(in_array($rolCabecera, ["Administrador", "contabilidad"], true))
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuSiiRol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">SII</a>
            <div class="dropdown-menu" aria-labelledby="menuSiiRol">
            <a class="dropdown-item" href="/sii/cargarfolios">Cargar Folios</a>
            <a class="dropdown-item" href="/sii/anularfolios">Anulación de folios</a>
            <a class="dropdown-item" href="/sii/estadodte">Estado DTE</a>
            {{-- <a class="dropdown-item" href="/sii/verestado">Estado de Envíos</a> --}}
            <a class="dropdown-item" href="/sii/ambiente">Ambiente Certificación</a>
            </div>
        </li>
          @endif

         
          
          @if($rolCabecera === "Administrador")
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuMantenimientoAdmin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
              <div class="dropdown-menu" aria-labelledby="menuMantenimientoAdmin">
                  <a class="dropdown-item" href="/marcavehiculo">Marca Vehículos</a>
                  <a class="dropdown-item" href="/modelovehiculo">Modelo Vehículos</a>
                  <a class="dropdown-item" href="/rol">Roles de Usuario</a>
                  <a class="dropdown-item" href="/familia">Familia de Repuestos</a>
                  <a class="dropdown-item" href="/marcarepuesto">Marca de Repuestos</a>
                  <a class="dropdown-item" href="/repuesto/listar-oems">Clonar OEMs</a>
                  <a href="/repuesto/clonaciones" class="dropdown-item">Clonaciones</a>
                  <a href="/repuesto/clonaciones_rv" class="dropdown-item">Clonaciones RV</a>
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
          @if(in_array($rolCabecera, ["bodega-venta", "vendedor", "Cajer@"], true))
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="menuMantenimientoBasico" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mantenimiento</a>
              <div class="dropdown-menu" aria-labelledby="menuMantenimientoBasico">
                  
                  <a class="dropdown-item" href="/clientes">Agregar Clientes</a>
                  
                  
              </div>
          </li>
          @endif
          @if(in_array($rolCabecera, ["Administrador", "contabilidad"], true))
          <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownLibroLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Libros</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownLibroLink">
              <a class="dropdown-item" href="/rcof/">RCOF Boletas</a>
              @if($usuarioCabecera->id !== 75) <a class="dropdown-item" href="/libro/ventas">Libro Ventas</a> @endif
              <a class="dropdown-item" href="/libro/compras">Libro Compras</a>
            </div>
          </li>
          @endif
          @if($rolCabecera === "Administrador")
          <li class="nav-item dropdown">
            <a href="javascript:void(0)" class="nav-link dropdown-toggle" id="navbarDropdownReportesLink" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">Reportes</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdownReportesLink">
              <a class="dropdown-item" href="/reportes/ventasdiarias">Ventas diarias</a>
              <a class="dropdown-item" href="/usuarios/rendimiento">Desempeño mensual</a>
               <a class="dropdown-item" href="/reportes/informe-rentabilidad">Informe Rentabilidad (prueba)</a>
              <a class="dropdown-item" href="/reportes/documentosgenerados">Documentos generados</a>
              <a class="dropdown-item" href="/reportes/documentosgenera2">Buscar documentos</a>
              <a class="dropdown-item" href="/reportes/transbank">Documentos Banco Estado</a>
              <a class="dropdown-item" href="/reportes/getnet">Documentos Getnet</a>
              <a class="dropdown-item" href="/reportes/transferencias">Documentos Transferencias</a>
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
       @if($rolCabecera !== "vendedor" || $usuarioCabecera->id == 250)

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center"
       href="javascript:void(0)"
       id="menuNotificacionesRoles"
       data-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">

        <img src="{{ asset('storage/imagenes/campana.png') }}"
             alt=""
             style="width: 25px;"
             class="mr-1">

        Notificaciones
    </a>

    <div class="dropdown-menu dropdown-menu-right"
         aria-labelledby="menuNotificacionesRoles">

        <a href="/reportes/rep_sin_prov"
           class="dropdown-item d-flex justify-content-between align-items-center">
            Sin Stock Prov
            <span class="d-none badge badge-warning"
                  id="cantidad_sin_prov"></span>
        </a>

        <a href="/reportes/bajo_stock"
           class="dropdown-item d-flex justify-content-between align-items-center">
            Stock Mínimo
            <span class="d-none badge badge-warning"
                  id="cantidad_stock_minimo"></span>
        </a>

        <a href="/reportes/stock_critico"
           class="dropdown-item d-flex justify-content-between align-items-center">
            Stock Crítico
            <span class="d-none badge badge-warning"
                  id="cantidad_stock_critico"></span>
        </a>

        <a href="/reportes/stock_pendiente"
           class="dropdown-item d-flex justify-content-between align-items-center">
            Stock Pendiente
            <span class="d-none badge badge-warning"
                  id="cantidad_stock_pendiente"></span>
        </a>

        <div class="dropdown-divider"></div>

        <a href="javascript:void(0)"
           onclick="dameofertas()"
           class="dropdown-item">
            Ofertas
        </a>

    </div>
</li>

@endif
      @if($rolCabecera !== "Bodeguer@")
      <div class="d-none text-white letra_pequeña" id="porcentaje_ventas"></div>
      @endif
      <ul class="navbar-nav">
        
      <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle d-flex align-items-center" href="javascript:void(0)" id="menuUsuarioRol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de usuario">
        @if($usuarioCabecera->image_path)
          <img src="{{ url('usuarios/avatar/'.$usuarioCabecera->image_path) }}" alt="Foto de {{ $usuarioCabecera->name }}" id="logo">
        @else
          <i class="fas fa-user-circle fa-2x" aria-hidden="true"></i>
        @endif
      </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="menuUsuarioRol">
            <span class="dropdown-item-text text-center">{{ $usuarioCabecera->name }} ({{ $rolCabecera }})</span>
            @if($rolCabecera === "Administrador") <a href="{{url('usuarios/edit/'.$usuarioCabecera->id)}}" class="dropdown-item">Editar usuario</a> @endif
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

