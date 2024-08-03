<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: rgb(0, 0, 0); color: white;">
          <h5 class="modal-title" id="exampleModalLabel" >Búsqueda Repuesto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="background-color: rgb(242, 245, 169); height: 500px;">
          <div class="form-group d-flex">
                <input type="search" class="form-control" id="descripcionRepuesto" style="border-radius: 100px;" onkeyup="enter_press(event)" placeholder="Ingrese una descripción del repuesto">
                <button class="btn btn-success btn-sm" onclick="buscarRepuesto()"><i class="fa-solid fa-magnifying-glass"></i></button>
          </div>
          <div id="resultado" class="scrollable-modal-body">

          </div>
        </div>
        <div class="modal-footer" style="background: rgb(0, 0, 0)">
          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>