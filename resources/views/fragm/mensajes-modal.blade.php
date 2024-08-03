<style>
    .modal-cabezera{
        height: 40px;
        color:royalblue;
        background-color:wheat;
        padding-top:5px;
        padding-bottom:5px;
    }
    .modal-piez{
        padding-top:5px;
        padding-bottom:5px;
        background-color:wheat;
    }
    .modal-dialog-15{
        margin-top: 15%;
        height: auto;
    }

.modal-badi,
.modal-contento {
    height: auto;
}
</style>
<div class="modal fade" data-backdrop="static" id="menzajez-modal" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-15">
        <div class="modal-content modal-contento">
            <div class="modal-header modal-cabezera">
            <!-- boton cerrar no deseo por ahora
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            -->
            <h4 class="modal-title text-center"><b>ATENCIÓN!!!</b></h4>
            </div>
            <div class="modal-body modal-badi">
            <center><img src="{{asset('storage/imagenes/wd.gif')}}"></center>
            <h3 id="aqui_mensaje" class="text-center">MENSAJE</h3>
            </div>
            <div class="modal-footer modal-piez">
                <button type="button" class="btn btn-info btn-sm" style="height:20px" data-dismiss="modal"><b>SALIR</b></button>
            </div>
        </div>
    </div>
</div>
