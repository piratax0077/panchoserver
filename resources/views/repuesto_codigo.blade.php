<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laravel Generate Barcode Examples</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>

<body>
    
    @foreach($repuestos as $repuesto)
        @php
            $cod_int = intval($repuesto->codigo_interno);
        @endphp
        <div style="margin: 20px;">
            {!! DNS1D::getBarcodeHTML($cod_int, 'CODABAR') !!}
        </div>
        <div style="padding-top: 10px; padding-bottom: 20px; width: 24%;">
           {{$repuesto->codigo_interno}} - {{$repuesto->descripcion}}
        </div>
    @endforeach
</body>
</html>