<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Combito</title>
    <script src="{{asset('jquery/jquery-3.2.1.js')}}"></script>
    <!--
    <script src="{{asset('msdropdown/js/jquery-1.9.0.min.js')}}"></script>
    -->
    <link rel="stylesheet" href="{{asset('msdropdown/css/ddd.css')}}">
    <script type='text/javascript' src="{{asset('msdropdown/js/jquery.dd.min.js')}}"></script>
    <script type='text/javascript'>
        function vervalor(id)
        {
            document.getElementById("valor").value="valorcito";
        }
             
    </script>
/head>
<body>
        <label for="prueba">Combito:</label>
        <select name="prueba" id="prueba" style="width:350px">
            <option value="" data-description="Elija una opción">Fotos de Algo</option>
            <option data-image="{{asset('storage/fotozzz/000c4bb1305d1bf08e18080f138c3365.png')}}" data-description="Es una pruebita" value="1">Uno</option>
            <option data-image="{{asset('storage/fotozzz/0a63bd5210e52dc318e0c4fc6f0d9155.png')}}" value="2">Dos</option>
            <option data-image="{{asset('storage/fotozzz/0aab5a6c2df5b13a72c2a2e48ca26b28.png')}}" value="3">Tres</option>
            <option data-image="{{asset('storage/fotozzz/0acfd9d78e4a7a909c26e9f81100dc2d.png')}}" value="4">Cuatro</option>
            <option data-image="{{asset('storage/fotozzz/0b04cd7873051218e2e72026598453d5.png')}}" value="5">Cinco</option>
            <option onmouseover="vervalor(this.value)" data-image="{{asset('storage/fotozzz/0b9dcd2202c6865adcf9240c8ba08e26.png')}}" value="6">Seis</option>
            <option data-image="{{asset('storage/fotozzz/0b421c2ca1dcb76b38c7922e1b5014ea.png')}}" value="7">Siete</option>
        </select>
        <br><br><br>
        <input type="text" value="" id="valor">
        <br><br>

        <label for="prueba2">Comboto:</label>
        <select name="prueba2" id="prueba2" onmouseover="vervalor(this.value)" >
            <option value="" title="nada">Elegir</option>
            <option value="1" title="Es como un tooltips globo de ayuda">Uno</option>
            <option value="2" title="Doooos">Dos larguito este</option>
            <option value="3">Tres</option>
            <option value="4">Cuatro</option>
            <option value="5">Cinco</option>
            <option value="6">Seis</option>
            <option value="7">Siete</option>
        </select>
</body>
</html>
<script>
        $(document).ready(function(e) {
            //$("#prueba").msDropdown({visibleRows:3});
            $("#prueba").msDropdown();
        });
</script>    