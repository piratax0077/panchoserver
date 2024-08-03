<nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-right:5px">
   <!--container fluid -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{url('login')}}">Repuestos</a>
    </div>

    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">

          <li class="dropdown">
              <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Ventas <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="{{url('ventas')}}">Ventas (Fact-Bole)</a></li>
                <li><a href="{{url('notacredito')}}">Nota de Crédito</a></li>
                <li><a href="{{url('notadebito')}}">Nota de Débito</a></li>
              </ul>
            </li>

        <li class="dropdown">
          <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Inventarios <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="{{url('factuprodu/crear')}}">Facturas de Compra</a></li>
            <li><a href="{{url('compras/listar')}}">Listar Facturas (Compras)</a></li>
          </ul>
        </li>



        <li class="dropdown">
          <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Mantenimiento <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <!-- cuando se pone url('marcavehiculo') va al index del controlador
              Para ir al create debe ponerse url('marcavehiculo/create')
             -->
            <li><span><img src="{{asset('storage/imagenes/wd.gif')}}" width="80px" /><a href="{{url('marcavehiculo')}}">Marca Vehículos</span></a></li>
            <li><a href="{{url('modelovehiculo')}}">Modelo Vehículos</a></li>
            <li><a href="{{url('rol')}}">Roles de Usuario</a></li>
            <li><a href="{{url('familia')}}">Familia de Repuestos</a></li>
            <li><a href="{{url('marcarepuesto')}}">Marca de Repuestos</a></li>
            <li><a href="{{url('repuesto/modificar')}}">Modificar Repuesto</a></li>
            <li><a href="{{url('repuesto')}}">Catálogo de Repuestos</a></li>
            <li><a href="{{url('pais')}}">Países</a></li>
            <li><a href="{{url('proveedor')}}">Proveedores</a></li>
            <li><a href="{{url('formapago')}}">Formas de Pago</a></li>
            <li><a href="{{url('limitecredito')}}">Límites de Crédito</a></li>
            <li><a href="{{url('diascredito')}}">Días de Crédito</a></li>
            <li><a href="{{url('clientes')}}">Clientes</a></li>
            <li><a href="{{url('parametros')}}">Parámetros</a></li>
            <li><a href="{{url('relacionados')}}">Repuestos Relacionados</a></li>
            <li role="separator" class="divider"></li>
            <li class="dropdown-header">Sistema</li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('usuarios')}}">Usuarios</a></li>
            <li><a href="{{url('usuarios/'.Session::get('usuario'))}}">Cambiar Clave</a></li>
            <li class="disabled"><a href="javascript:void(0)">Nada</a></li>
            <li><a href="javascript:void(0)">Algo mas</a></li>
          </ul>
        </li>

      </ul>


      <ul class="nav navbar-nav navbar-right">
        <li><a href="javascript:void(0)">Usuario: {{Session::get('usuario_id')}}</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="active"><a href="{{url('login/create')}}">Salir<span class="sr-only">(current)</span></a></li>
      </ul>

    </div><!--/.nav-collapse -->
<!--/.container-fluid -->
</nav>
