
<div class="row w-100">
    
    <div class="col-md-6 mb-4 tabla-scroll-y-400">
      <p class="text-center font-weight-bold">Días mas vendidos</p>
      <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">Fecha</th>
              <th scope="col">Total DTE</th>
              <th scope="col">Total Ventas</th>
            </tr>
          </thead>
          <tbody>
              @foreach($dias_mas_vendidos as $d)
              <tr>
                  <th scope="row">{{$d->fecha}}</th>
                  <td>{{$d->total}}</td>
                  <td>${{number_format($d->total_pago,0,',','.')}}</td>
              </tr>
              @endforeach
          </tbody>
        </table>
    </div>
    <div class="col-md-6 mb-4 ">
      <canvas id="grafico7"  height="140px"></canvas>
        
    </div>
    <div class="col-md-6 mb-4 tabla-scroll-y-400">
        <p class="text-center font-weight-bold">Mejores cliente</p>
        <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Rut</th>
                <th scope="col">Nombre completo</th>
                <th scope="col">Razón social</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody>
                @foreach($mejores_clientes as $c)
                <tr>
                    <th scope="row">{{$c->rut}}</th>
                    <td>{{$c->nombres}} {{$c->apellidos}}</td>
                    <td>{{$c->razon_social}}</td>
                    <td>{{$c->total}}</td>
                </tr>
                @endforeach
            </tbody>
          </table>
          
    </div>
    <div class="col-md-6 mb-4">
      <canvas id="grafico3" height="140px;"></canvas>
    </div>
    <div class="col-md-6 mb-4 tabla-scroll-y-400">
        <p class="text-center font-weight-bold">Marcas más vendidas</p>
        <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Marca Repuesto</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody>
                @foreach($marcas_mas_vendidas as $m)
                <tr>
                    <th scope="row">{{$m->marcarepuesto}}</th>
                    <td>{{$m->total}}</td>
                </tr>
                @endforeach
            </tbody>
          </table>
          
    </div>
    <div class="col-md-6 mb-4">
      <canvas id="grafico5" height="140px;"></canvas>

    </div>
    
    <div class="col-md-6 mb-4 tabla-scroll-y-400">
      <p class="text-center font-weight-bold">Repuestos más vendidos</p>
      <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col">Código interno</th>
              <th scope="col">Descripción</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody>
              @foreach($rep_mas_vendidos as $r)
              <tr>
                  <th scope="row">{{$r->codigo_interno}}</th>
                  <td>{{$r->descripcion}}</td>
                  <td>{{$r->total}}</td>
              </tr>
              @endforeach
          </tbody>
        </table>
        
    </div>
    <div class="col-md-6 mb-4">
      <canvas id="grafico" height="140px;"></canvas>
    </div>
  
</div>

<script>

  var labels = {!! $labels !!};
  var data = {!! $data !!};


  var labels3 = {!! $labels_cb !!};
  var data3 = {!! $data_cb !!};


  var labels5 = {!! $labels_mmv !!};
  var data5 = {!! $data_mmv !!};

  var labels7 = {!! $labels_dias_mas_vendidos !!};
  var data7 = {!! $data_dias_mas_vendidos !!};


  var mi_primer_grafico =
  {
      type:"bar",
      data:{
        datasets:[{
            label:'Repuestos más vendidos',
          data:data,
          backgroundColor: [
            "#04B404","#FFBF00",  "#FF0000",  "#04B4AE","#eee","aqua","pink","brown",'#000',"orange"
           ],
        }],
        labels: labels
      },
      options:{
        scales: {
                    y: {
                        beginAtZero: true // Iniciar el eje Y en 0,
                    }
                }
      }
  }
    var primer_grafico = document.getElementById('grafico').getContext('2d');
    window.pie = new Chart(primer_grafico,mi_primer_grafico);

    /* -------------------------------------------------------------------------------------------------------- */
    var mi_tercer_grafico ={
      type:"bar",
      data:{
        datasets:[{
          label:'Mejores clientes',
          data:data3,
          backgroundColor: [
            "#04B404","#FFBF00",  "#FF0000",  "#04B4AE","#eee","aqua","pink","brown",'#000',"orange"
           ],
        }],
        labels: labels3
      },
      options:{
        responsive: true,
        scales: {
                    y: {
                        beginAtZero: true // Iniciar el eje Y en 0,
                    }
                }
      }
    }
    var tercer_grafico = document.getElementById('grafico3').getContext('2d');
    window.pie = new Chart(tercer_grafico,mi_tercer_grafico);

    /* -------------------------------------------------------------------------------------------------------- */
    var mi_quinto_grafico ={
      type:"bar",
      data:{
        datasets:[{
          label:'Marcas más vendidas',
          data:data5,
          backgroundColor: [
            "#04B404","#FFBF00",  "#FF0000",  "#04B4AE","#eee","aqua","pink","brown",'#000',"orange"
           ],
        }],
        labels: labels5
      },
      options:{
        responsive: true,
        scales: {
                    y: {
                        beginAtZero: true // Iniciar el eje Y en 0,
                    }
                }
      }
    }
    var quinto_grafico = document.getElementById('grafico5').getContext('2d');
    window.pie = new Chart(quinto_grafico,mi_quinto_grafico);

    /* -------------------------------------------------------------------------------------------------------- */
    var mi_septimo_grafico ={
      type:"line",
      data:{
        datasets:[{
          label:'Dias más vendidos',
          data:data7,
          backgroundColor: [
            "#04B404","#FFBF00",  "#FF0000",  "#04B4AE","#eee","aqua","pink","brown",'#000',"orange"
           ],
        }],
        labels: labels7
      },
      options:{
        responsive: true,
        scales: {
                    y: {
                        beginAtZero: true // Iniciar el eje Y en 0,
                    }
                }
      }
    }
    var septimo_grafico = document.getElementById('grafico7').getContext('2d');
    window.pie = new Chart(septimo_grafico,mi_septimo_grafico);

    
</script>