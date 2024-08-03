@extends('plantillas.app')
@section('titulo','Buscar factura (Compras)')
@section('javascript')
    <script>
        function soloNumeros(e)
        {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8))
        }

        function eliminar_factura(id_factura){
        
        Vue.swal({
        text: "¿ Desea ELIMINAR la factura ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'CONTINUAR',
        cancelButtonText: 'CANCELAR'
        }).then((result) => {
        if (result.isConfirmed) {
          let url = '{{url("ventas/eliminar_factura")}}'+'/'+id_factura;
          $.get({
            type:'get',
            url: url,
            success: function(resp){
              
              if(resp == 'OK'){
                Vue.swal({
                  icon:'success',
                  text:'Factura eliminada correctamente',
                  toast: true,
                  showConfirmButton: false,
                  timer: 3000,
                  position:'top-end'
                });
                dameFacturasDelProveedor();
              }else{
                console.log(resp);
                return false;
              }
              
            },
            error: function(error){
              console.log(error.responseText);
            }
          });
        }else{
        }
        
      });
    }


    
        function buscar(){
            let num_factura = document.getElementById('num_factura').value;
            if(num_factura.trim() == 0 || num_factura == ''){
                Vue.swal({
                    icon:'error',
                    position:'top-end',
                    text:'Debe ingresar numero de factura',
                    showConfirmButton: false,
                    toast:true,
                    timer: 3000
                });
                return false;
            }
           let url = '/compras/listar_factura_numero/'+num_factura;

           $.ajax({
               type:'get',
               url:url,
               success: function(resp){
               
                // return false;
                $('#data').html(resp);
               },
               error: function(error){
                console.log(error.responseText);
               }
           })
        }
    </script>
@endsection
@section('style')
    <style>
        .formulario{
            /* display: flex; */
            width: 100%;
            margin: 10px auto;
            text-align: center;
            border: 1px solid black;
            align-items: center;
            padding: 40px;
            background: #f2f4a9;
            border-radius: 30px;
        }

        .table{
            background: #e6f2ff;
        }

        .letra_pequeña{
            font-size:13px;
        }
    </style>
@endsection
@section('contenido_titulo_pagina')
<center><h4 class="titulazo">Buscar Factura (Compras)</h4></center><br>
@endsection
@section('contenido_ingresa_datos')
<div class="container-fluid">
    <img src="{{asset('storage/imagenes/logo_pos.png')}}" alt="" class="logo_">
  <div class="row">
    <div class="col-md-4">
      <div class="formulario">
        <div class="form-group">
            <label for="">Número de factura</label>
            <input type="text" name="" id="num_factura" class="form-control" onKeyPress="return soloNumeros(event)">
        </div>
        <input type="button" value="Buscar" class="btn btn-success btn-sm"  onclick="buscar()">
    </div>
    </div>
    <div class="col-md-8">
      <div id="data">

      </div>
    </div>
  </div>
    
    
        
            
       
    </div>
    
</div>
@endsection