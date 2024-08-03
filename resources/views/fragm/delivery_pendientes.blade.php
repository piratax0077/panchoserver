@if($delivery_pendientes->count()>0)
    <center>
        <h4 style="color:red">HAY PAGOS PENDIENTES DE DELIVERY</h4>
        <br>
        <a href="{{url('reportes/ventasdiarias')}}" target="_blank">
            <h5 style="color:blue">REVISAR EN VENTAS DIARIAS</h5>
        </a>
    </center>
@endif
