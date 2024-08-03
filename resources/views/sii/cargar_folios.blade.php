@extends('plantillas.app')
@section('titulo','Cargar Folios')
@section('javascript')
<script type="text/javascript">

    function cargar_documentos(local_id){
        let url = 'damedocumentos'+'/'+local_id;
        $.ajax({
            type:'get',
            url: url,
            success: function(response){
                
                if(response.length>0)
                {
                    if(response === "cero"){
                        $('#tbody_documentos').empty();
                        $('#cargar_caf').empty();
                        console.log('no hay datos');
                    }else{
                        console.log(response);
                        $('#tbody_documentos').empty();
                        response.forEach(doc => {
                            $('#tbody_documentos').append(`
                            <tr> 
                                <td><input type="radio" value="`+doc.id+`" name="iddocumento" /> </td>    
                                <td>`+doc.nombre+` </td>
                                <td>`+doc.archivo+` </td>
                                <td>`+doc.fecha+`</td>
                                <td>`+doc.desde+` - `+doc.hasta+`</td>
                                <td>`+doc.actual+`</td>
                            </tr>
                            `);
                            });
                            $('#cargar_caf').empty();
                            $('#cargar_caf').append(`
                            <label for="XML">Elegir archivo CAF</label>
                            <input type="file" name="XML" value="XML" id="XML"><br>
                            <button class="btn btn-sm btn-primary" onclick="cargar_caf()">Cargar Archivo CAF</button>
                            `);
                        } 
                }
            }
        })
    }

    function cargar_caf(){
        //validar datos
        var archivo=$('#XML')[0].files[0];
            if(archivo===null || archivo===undefined)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Seleccione un archivo CAF de SII',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }else if(archivo.name.substr(archivo.name.length-3).toLowerCase()!='xml'){
                Vue.swal({
                    icon: 'error',
                    text:'Seleccione un archivo en formato XML',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }else{

            }

            var idlocal=$('#locales').val();
            if(idlocal===null || idlocal==0)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Debe seleccionar un local',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }

            var iddocu=$('input:radio[name=iddocumento]:checked').val();
            
            if(iddocu===null || iddocu==0 || iddocu === undefined)
            {
                Vue.swal({
                    icon: 'error',
                    text:'Debe seleccionar un documento',
                    position: 'top-end',
                    toast:true,
                    showConfirmButton: false,
                    timer: 3000,
                });
                return false;
            }

            var datos=new FormData();
            datos.append('archivo',archivo);
            datos.append('idlocal',idlocal);
            datos.append('iddocu',iddocu);

            var url_caf = '/sii/guardarcaf';
        
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
                });
           
            $.ajax({
                type:'POST',
                url: url_caf,
                data:datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response){
                   
                    if(response==idlocal)
                    {
                        cargar_documentos(response);
                        Vue.swal({
                            icon: 'success',
                            text:'CAF registrado',
                            position: 'top-end',
                            toast:true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    }
                    if(response=='-1')
                    {
                        Vue.swal({
                            icon: 'error',
                            text:'No coincide tipo DTE',
                            position: 'top-end',
                            toast:true,
                            showConfirmButton: false,
                            timer: 4000,
                        });
                    }
                    if(response=='-2')
                    {
                        Vue.swal({
                            icon: 'error',
                            text:'Archivo CAF ya existe',
                            position: 'top-end',
                            toast:true,
                            showConfirmButton: false,
                            timer: 4000,
                        });
                    }
                },  
                error: function(err){
                    console.log(err);
                }
            })
    }
</script>
@endsection
@section('contenido')
    {{-- <cargarfolios></cargarfolios> --}}
    <div class="container-fluid">
        <label for="locales">Elija un Local:</label>
        <select  id="locales"  onchange="cargar_documentos(this.value)">
            <option value="0">Locales:</option>
            @foreach ($locales as $local)
                <option value="{{$local->id}}">{{$local->local_nombre}} - {{$local->local_direccion}}</option>
            @endforeach
        </select>
        
    </div>
    <table class="table table-sm table-hover">
        <thead>
            <th></th>
            <th>Documento</th>
            <th>Archivo CAF</th>
            <th>Fecha Autoriza</th>
            <th>Rango</th>
            <th>Núm Actual</th>
        </thead>
        <tbody id="tbody_documentos">

        </tbody>
    </table>
    <div id="cargar_caf">

    </div>
    

@endsection
