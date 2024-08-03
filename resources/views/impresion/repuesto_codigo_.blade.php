<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laravel Generate Barcode Examples</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<style>
    
    .contenedor_codigo{
        width: 30%;
        height: 94px;
        margin: 0px auto;
        border: 1px solid black;
        text-align: center;
    }
    .logo{
        width: 30%;
        margin: 0px auto;
    }

    .codigo_barras, .codigo_interno{
        width: 90%;
        margin: 0px auto;
    }
    .descripcion{
        font-weight: bold;
        text-transform: uppercase;
        font-size: 5px;
        width: 100%;
    }

    .codigo_interno{
        font-size: 5px;
    }
</style>
<body>
    @foreach($repuestos as $repuesto)
        @php
            $id_repuesto = intval($repuesto->id);
            $id_repuesto_string = strval($id_repuesto);
        @endphp
        <div class="contenedor_codigo">
            <div class="logo" >
                <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="">
            </div>
            <div class="descripcion">
                <p>{{$repuesto->descripcion}}  </p>
                @if ($repuesto->local_id === 1)
                    <p>{{$repuesto->ubicacion}}</p>
                @elseif($repuesto->local_id_dos === 1)
                    <p>{{$repuesto->ubicacion_dos}}</p>
                @endif
                
            </div>
            
            <div class="codigo_barras">
                <div>
                    {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
                    {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($id_repuesto_string,"C128",1.4,22)}}" alt="barcode" /><br>
                </div>
                
            </div>
            <div class="codigo_interno">
                <p>{{$repuesto->codigo_interno}}</p>
            </div>
        </div>
        <div class="contenedor_codigo">
            <div class="logo" >
                <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="">
            </div>
            <div class="descripcion">
                <p>{{$repuesto->descripcion}}  </p>
                @if ($repuesto->local_id === 1)
                    <p>{{$repuesto->ubicacion}}</p>
                @elseif($repuesto->local_id_dos === 1)
                    <p>{{$repuesto->ubicacion_dos}}</p>
                @endif
                
            </div>
            
            <div class="codigo_barras">
                <div>
                    {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
                    {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($id_repuesto_string,"C128",1.4,22)}}" alt="barcode" /><br>
                </div>
                
            </div>
            <div class="codigo_interno">
                <p>{{$repuesto->codigo_interno}}</p>
            </div>
        </div>
        <div class="contenedor_codigo">
            <div class="logo" >
                <img src="{{asset('storage/imagenes/logoOficial.jpeg')}}" alt="">
            </div>
            <div class="descripcion">
                <p>{{$repuesto->descripcion}}  </p>
                @if ($repuesto->local_id === 1)
                    <p>{{$repuesto->ubicacion}}</p>
                @elseif($repuesto->local_id_dos === 1)
                    <p>{{$repuesto->ubicacion_dos}}</p>
                @endif
                
            </div>
            
            <div class="codigo_barras">
                <div>
                    {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
                    {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($id_repuesto_string,"C128",1.4,22)}}" alt="barcode" /><br>
                </div>
                
            </div>
            <div class="codigo_interno">
                <p>{{$repuesto->codigo_interno}}</p>
            </div>
        </div>
    @endforeach
</body>
</html>