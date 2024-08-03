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
        width: 1px;
        float: left;
        height: 94px;
        
        display: block;
        margin: 10px !important;
        margin-top: 5px !important;
    }
    .logo{
        width: 30%;
        margin: 0px auto;
    }

    .codigo_barras, .codigo_interno{
        width: 100%;
        text-align: center;
    }
    .descripcion{
        font-weight: bold;
        text-transform: uppercase;
        font-size: 9px;
        text-align: center;
    }

    .codigo_interno{
        font-size: 8px;
    }
</style>
<body>
    @foreach($repuestos as $repuesto)
        @php
            $id_repuesto = intval($repuesto->codigo_interno);
            $id_repuesto_string = strval($id_repuesto);
        @endphp
        <div class="contenedor_codigo">
            <div class="logo" >
                <img src="https://panchorepuestos.cl/storage_original/imagenes/logo_pos.png" alt="">
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
                    {{-- {!! DNS1D::getBarcodeHTML($id_repuesto, 'C39') !!} --}}
                    {{-- @php echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG("4", "C39+",3,33) . '" alt="barcode"   />'; @endphp --}}
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($repuesto->codigo_interno,"C128",1.4,22)}}" alt="barcode" /><br>
            </div>
            <div class="codigo_interno">
                {{$repuesto->codigo_interno}}
            </div>
        </div>
        
        
    @endforeach
</body>
</html>