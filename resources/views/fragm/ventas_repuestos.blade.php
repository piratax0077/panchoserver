<!-- Carga en el div con id zona_grilla en ventas_principal.blade.php
abajo en el modal -->

<style>
#zona_grilla .letra_pequeña { font-family: inherit; font-size: 13px; }
#zona_grilla .row_cero_margen { margin-right: 0; margin-left: 0; }
#zona_grilla .resultados-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 14px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}
#zona_grilla .resultados-toolbar__texto { margin: 0; color: #475569; font-size: 13px; }
#zona_grilla .resultados-toolbar__vistas {
    display: inline-flex;
    gap: 4px;
    padding: 3px;
    border-radius: 8px;
    background: #eef2f7;
}
#zona_grilla .btn-vista {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 31px;
    border: 0;
    border-radius: 6px;
    color: #64748b;
    background: transparent;
    cursor: pointer;
}
#zona_grilla .btn-vista:hover { color: #2563eb; background: #fff; }
#zona_grilla .btn-vista.is-active {
    color: #fff;
    background: #2563eb;
    box-shadow: 0 2px 6px rgba(37, 99, 235, .25);
}
#zona_grilla .resultados-tabla {
    height: 100%;
    overflow: auto;
    background: #f8fafc;
}
#zona_grilla .resultados-tabla table {
    min-width: 1420px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
}
#zona_grilla .resultados-tabla thead th {
    position: sticky;
    top: 0;
    z-index: 4;
    padding: 10px 8px;
    border: 0;
    border-bottom: 2px solid #cbd5e1;
    color: #334155;
    background: #e8f5f2 !important;
    font-weight: 700;
    line-height: 1.2;
    vertical-align: middle;
    white-space: normal;
}
#zona_grilla .resultados-tabla tbody td {
    padding: 9px 8px;
    border-top: 0;
    border-bottom: 1px solid #e8edf3;
    color: #1f2937;
    background: #fff;
    line-height: 1.35;
    vertical-align: top;
}
#zona_grilla #tbl_repuestos_imagenes {
    width: 100% !important;
    min-width: 0 !important;
    table-layout: fixed;
}
#zona_grilla #tbl_repuestos_imagenes th,
#zona_grilla #tbl_repuestos_imagenes td {
    padding: 9px 6px;
    overflow-wrap: normal;
    word-break: normal;
    vertical-align: middle;
}
#zona_grilla #tbl_repuestos_imagenes th { text-align: left; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(1),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(1) { width: 7%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(2),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(2) { width: 7%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(3),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(3) { width: 8%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(4),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(4) { width: 13%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(5),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(5) { width: 10%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(6),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(6) { width: 6%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(7),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(7) { width: 6%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(8),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(8) { width: 9%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(9),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(9) { width: 7%; text-align: center; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(10),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(10) { width: 6%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(11),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(11) { width: 9%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(12),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(12) { width: 7%; }
#zona_grilla #tbl_repuestos_imagenes th:nth-child(13),
#zona_grilla #tbl_repuestos_imagenes td:nth-child(13) { width: 5%; text-align: center; }
#zona_grilla #tbl_repuestos_imagenes .form-control-sm {
    width: 100%;
    min-width: 0;
}
#zona_grilla #tbl_repuestos_imagenes td:nth-child(12) strong { white-space: nowrap; }
#zona_grilla #tbl_repuestos_imagenes .acciones-compactas {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
#zona_grilla #tbl_repuestos_imagenes .acciones-compactas .btn {
    display: inline-flex;
    width: 32px;
    min-width: 32px;
    height: 32px !important;
    padding: 0 !important;
    align-items: center;
    justify-content: center;
}
#zona_grilla #tbl_repuestos_imagenes td.precio-compacto {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    white-space: nowrap;
}
#zona_grilla #tbl_repuestos_imagenes .precio-compacto > a {
    display: block;
    text-decoration: none;
}#zona_grilla #tbl_repuestos_imagenes .precio-compacto .badge {
    display: table;
    margin: 0 auto 6px;
}
#zona_grilla #tbl_repuestos_imagenes .precio-compacto__valor {
    display: block;
    width: 100%;
    color: #172033;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.2;
    text-align: center;
}
#zona_grilla .resultados-tabla tbody tr:nth-child(even) td { background: #f8fafc; }
#zona_grilla .resultados-tabla tbody tr:hover td:not([id^="dias_precio-"]) {
    background: #eff6ff !important;
}
#zona_grilla .resultados-tabla tbody tr[bgcolor] td:not([id^="dias_precio-"]) {
    background: #fff1f2 !important;
}
#zona_grilla .resultados-tabla td[id^="dias_precio-"] {
    min-width: 64px;
    font-weight: 700;
    vertical-align: middle;
}
#zona_grilla .resultados-tabla .form-control-sm { min-width: 76px; height: 29px !important; }
#zona_grilla .resultados-tabla .btn-sm { min-height: 30px; height: auto !important; padding: 4px 9px !important; }
#zona_grilla .imagen_pequeña {
    width: 130px;
    height: 105px;
    padding: 5px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    object-fit: contain;
    background: #fff;
    box-shadow: 0 3px 10px rgba(15, 23, 42, .09);
    transition: transform .18s ease, box-shadow .18s ease;
}
#zona_grilla a:hover .imagen_pequeña {
    transform: translateY(-2px);
    box-shadow: 0 7px 18px rgba(15, 23, 42, .16);
}
#zona_grilla .resultados-layout {
    display: flex;
    min-height: 0;
    flex-wrap: nowrap;
    align-items: stretch;
    background: #f8fafc;
}
#zona_grilla .resultados-layout #rezultadoz {
    flex: 1 1 auto;
    width: auto;
    min-width: 0;
    max-width: none;
}
#zona_grilla #tabla_info_imagenes.panel-precio-activo {
    display: flex !important;
    flex-wrap: nowrap;
    align-items: stretch;
    min-height: 0;
}
#zona_grilla #tabla_info_imagenes.panel-precio-activo #rezultadoz_imagenes {
    flex: 1 1 auto;
    width: auto !important;
    min-width: 0;
    max-width: none;
}
#zona_grilla #modifikar_precio {
    position: relative;
    flex: 0 0 clamp(560px, 36vw, 680px);
    width: clamp(560px, 36vw, 680px);
    max-width: clamp(560px, 36vw, 680px);
    margin: 0;
    padding: 0 !important;
    border: 0;
    border-left: 1px solid #dbe3ed;
    border-radius: 0;
    background: #fff;
    box-shadow: -8px 0 24px rgba(15, 23, 42, .07);
}
#zona_grilla .editor-precio__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
}
#zona_grilla .editor-precio__titulo { margin: 0; color: #172033; font-size: 16px; font-weight: 700; }
#zona_grilla .editor-precio__cerrar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0 !important;
    border: 0;
    border-radius: 7px;
    color: #64748b;
    background: #f1f5f9;
}
#zona_grilla .editor-precio__cerrar:hover { color: #fff; background: #dc3545; }
#zona_grilla .editor-precio__contenido { padding: 15px 16px; }
#zona_grilla .editor-precio__aviso {
    margin: 0 0 16px;
    padding: 10px 12px;
    border-left: 4px solid #f59e0b;
    border-radius: 7px;
    color: #854d0e;
    background: #fffbeb;
    font-size: 12px;
    line-height: 1.4;
}
#zona_grilla .editor-precio__label { display: block; margin-bottom: 6px; color: #475569; font-size: 12px; font-weight: 700; }
#zona_grilla .editor-precio__form { display: flex; align-items: center; gap: 7px; margin-bottom: 18px; }
#zona_grilla .editor-precio__form #precio_a_modificar {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    height: 38px;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    text-align: right;
}
#zona_grilla .editor-precio__form .btn { flex: 0 0 38px; width: 38px; height: 38px !important; padding: 0 !important; }
#zona_grilla .editor-precio__compras-titulo {
    margin: 0 0 9px;
    padding-top: 14px;
    border-top: 1px solid #e2e8f0;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}
#zona_grilla #compras_repuesto {
    max-height: 310px;
    padding: 0;
    overflow-x: hidden;
    overflow-y: auto;
}
#zona_grilla #compras_repuesto table {
    width: 100% !important;
    min-width: 0;
    table-layout: fixed;
    font-size: 11px;
}
#zona_grilla #compras_repuesto thead th,
#zona_grilla #compras_repuesto tbody td {
    width: auto !important;
    padding: 7px 5px;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
}
#zona_grilla #compras_repuesto thead th {
    position: static;
    vertical-align: bottom;
}
#zona_grilla .resultados-layout.panel-precio-activo .resultados-tabla table { min-width: 1320px; }
#zona_grilla #tbl_repuestos {
    display: block;
    min-width: 0;
    width: 100%;
    table-layout: auto;
    background: transparent;
}
#zona_grilla #tbl_repuestos thead { display: none; }
#zona_grilla #tbl_repuestos tbody { display: block; padding: 10px; }
#zona_grilla #tbl_repuestos tbody tr {
    display: grid;
    grid-template-columns: repeat(16, minmax(0, 1fr));
    gap: 0;
    margin-bottom: 10px;
    overflow: hidden;
    border: 1px solid #dce4ed;
    border-radius: 10px;
    background: #fff;
    background-color: #fff !important;
    box-shadow: 0 2px 7px rgba(15, 23, 42, .05);
}
#zona_grilla #tbl_repuestos .resultado-imagen {
    grid-column: 1 / 3;
    grid-row: 1 / 4;
    display: flex;
    min-height: 150px;
    align-items: center;
    justify-content: center;
    padding: 10px;
    background: #f8fafc !important;
}
#zona_grilla #tbl_repuestos .resultado-imagen__enlace {
    display: flex;
    width: 100%;
    height: 100%;
    min-height: 130px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #dbe4ee;
    border-radius: 9px;
    background: #fff;
}
#zona_grilla #tbl_repuestos .resultado-imagen img {
    width: 100%;
    height: 140px;
    padding: 6px;
    object-fit: contain;
    transition: transform .2s ease;
}
#zona_grilla #tbl_repuestos .resultado-imagen__enlace:hover img { transform: scale(1.04); }
#zona_grilla #tbl_repuestos .resultado-imagen__vacia {
    display: flex;
    min-height: 130px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    color: #94a3b8;
    font-size: 11px;
}
#zona_grilla #tbl_repuestos .resultado-imagen__vacia i { font-size: 32px; }
#zona_grilla #tbl_repuestos tbody td {
    min-width: 0;
    padding: 9px 10px;
    border: 0;
    border-right: 1px solid #edf1f5;
    background: #fff;
    overflow-wrap: anywhere;
}
#zona_grilla #tbl_repuestos tbody tr:nth-child(even) td { background: #fff; }
#zona_grilla #tbl_repuestos tbody tr[bgcolor] td:not([id^="dias_precio-"]) { background: #fff7f7 !important; }
#zona_grilla #tbl_repuestos .resultado-celda::before {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .03em;
    line-height: 1.1;
    text-transform: uppercase;
}
#zona_grilla #tbl_repuestos .resultado-codigo { grid-column: 3 / 4; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-codigo::before { content: "Código"; }
#zona_grilla #tbl_repuestos .resultado-codigo-secundario { grid-column: 4 / 5; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-codigo-secundario::before { content: "OEM"; }
#zona_grilla #tbl_repuestos .resultado-proveedor { grid-column: 4 / 7; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-proveedor::before { content: "Proveedor"; }
#zona_grilla #tbl_repuestos .resultado-descripcion { grid-column: 7 / 12; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-descripcion::before { content: "Descripción"; }
#zona_grilla #tbl_repuestos .resultado-medida { grid-column: 12 / 14; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-medida::before { content: "Medida"; }
#zona_grilla #tbl_repuestos .resultado-marca { grid-column: 14 / 17; grid-row: 1; }
#zona_grilla #tbl_repuestos .resultado-marca::before { content: "Marca y origen"; }
#zona_grilla #tbl_repuestos .resultado-grupo-principal {
    display: block;
    color: #1f2937;
    font-weight: 700;
}
#zona_grilla #tbl_repuestos .resultado-grupo-secundario {
    display: flex;
    align-items: baseline;
    gap: 7px;
    margin-top: 8px;
    padding-top: 7px;
    border-top: 1px dashed #dbe3ec;
    color: #475569;
}
#zona_grilla #tbl_repuestos .resultado-grupo-secundario small {
    flex: 0 0 auto;
    color: #64748b;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
}
#zona_grilla #tbl_repuestos .resultado-grupo-secundario strong {
    min-width: 0;
    color: #334155;
    font-size: 12px;
    overflow-wrap: anywhere;
}
#zona_grilla #tbl_repuestos .resultado-precio {
    grid-column: 3 / 6;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: left !important;
}
#zona_grilla #tbl_repuestos .resultado-precio::before { content: "Precio de venta"; }
#zona_grilla #tbl_repuestos .resultado-precio p {
    align-items: center;
    gap: 4px;
    margin: 0;
    color: #0f172a;
    font-size: 21px;
    font-weight: 800;
}
#zona_grilla #tbl_repuestos .resultado-agregar {
    grid-column: 12 / 14;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center !important;
}
#zona_grilla #tbl_repuestos .resultado-agregar::before { content: "Carrito"; }
#zona_grilla #tbl_repuestos .resultado-agregar .btn {
    width: 100%;
    min-height: 38px;
    font-weight: 700;
}
#zona_grilla #tbl_repuestos .resultado-stock-total { grid-column: 6 / 9; grid-row: 2; }
#zona_grilla #tbl_repuestos .resultado-stock-total::before { content: "Cantidad"; }
#zona_grilla #tbl_repuestos .resultado-stock-detalle { grid-column: 9 / 12; grid-row: 2; }
#zona_grilla #tbl_repuestos .resultado-stock-detalle::before { content: "Retirar desde"; }

#zona_grilla .stock-detallado-compacto { position: relative; height: 145px; vertical-align: middle !important; }
#zona_grilla .stock-detallado-compacto__select { position: absolute; top: 50%; left: 8px; right: 8px; transform: translateY(-50%); }
#zona_grilla .stock-detallado-compacto__select .form-control { width: 100%; margin: 0; }
#zona_grilla .stock-detallado-compacto > .estado-stock-compacto { position: absolute; top: calc(50% + 28px); left: 8px; right: 8px; margin-top: 0; }
#zona_grilla .estado-stock-compacto { display: inline-block; max-width: 100%; margin-top: 5px; padding: 4px 6px; white-space: normal; line-height: 1.15; text-align: center; }
#zona_grilla #tbl_repuestos .resultado-stock-total .form-control,
#zona_grilla #tbl_repuestos .resultado-stock-detalle .form-control {
    width: 100%;
    height: 36px !important;
    border-radius: 7px;
}
#zona_grilla #tbl_repuestos .resultado-stock-fecha {
    grid-column: 14 / 16;
    grid-row: 2 / 4;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
#zona_grilla #tbl_repuestos .resultado-stock-fecha::before { content: "Stock actualizado"; }
#zona_grilla #tbl_repuestos .resultado-dias {
    grid-column: 16 / 17;
    grid-row: 2 / 4;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
#zona_grilla #tbl_repuestos .resultado-dias::before { content: "Días"; color: inherit; }
#zona_grilla #tbl_repuestos .resultado-detalle { grid-column: 12 / 13; grid-row: 3; text-align: center; }
#zona_grilla #tbl_repuestos .resultado-modificar { grid-column: 13 / 14; grid-row: 3; text-align: center; }
#zona_grilla #tbl_repuestos .resultado-detalle .btn,
#zona_grilla #tbl_repuestos .resultado-modificar .btn { width: 100%; }
@media (max-width: 1199.98px) {
    #zona_grilla #tbl_repuestos tbody tr,
    #zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos tbody tr {
        display: flex;
        flex-wrap: wrap;
    }
    #zona_grilla #tbl_repuestos tbody td { flex: 1 1 25%; }
    #zona_grilla #tbl_repuestos .resultado-imagen { flex: 0 0 180px; min-height: 165px; }
    #zona_grilla #tbl_repuestos .resultado-descripcion,
    #zona_grilla #tbl_repuestos .resultado-medida { flex-basis: 50%; }
    #zona_grilla #tbl_repuestos .resultado-agregar,
    #zona_grilla #tbl_repuestos .resultado-dias,
    #zona_grilla #tbl_repuestos .resultado-detalle,
    #zona_grilla #tbl_repuestos .resultado-modificar { flex: 1 1 16.666%; }
}
#zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos tbody tr {
    display: flex;
    flex-wrap: wrap;
}
#zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos tbody td { flex: 1 1 25%; }
#zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos .resultado-imagen { flex: 0 0 180px; }
#zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos .resultado-descripcion,
#zona_grilla .resultados-layout.panel-precio-activo #tbl_repuestos .resultado-medida { flex-basis: 50%; }
@media (max-width: 991.98px) {
    #zona_grilla .resultados-layout { display: block; }
    #zona_grilla #tabla_info_imagenes.panel-precio-activo {
        display: block !important;
    }
    #zona_grilla #tabla_info_imagenes.panel-precio-activo #rezultadoz_imagenes {
        width: 100% !important;
    }
    #zona_grilla #modifikar_precio {
        width: 100%;
        max-width: none;
        border-top: 1px solid #dbe3ed;
        border-left: 0;
        box-shadow: 0 -6px 18px rgba(15, 23, 42, .06);
    }
}
@media (max-width: 767.98px) {
    #zona_grilla .resultados-toolbar { align-items: flex-start; padding: 8px 10px; }
    #zona_grilla .resultados-toolbar__texto { padding-top: 6px; }
    #zona_grilla .resultados-tabla table { min-width: 1280px; }
}
</style>
@if($repuestos->count()>0)
	@php
		$hay_foto=[];
	@endphp
	@foreach($tienen_foto as $f)
		@php $hay_foto[]=$f['id_repuestos']@endphp
	@endforeach
    @php
        $fotos_por_repuesto = [];
        foreach ($arreglo as $repuesto_con_foto) {
            if (!isset($fotos_por_repuesto[$repuesto_con_foto->id])) {
                $fotos_por_repuesto[$repuesto_con_foto->id] = $repuesto_con_foto->urlfoto;
            }
        }
    @endphp

    <div class="resultados-toolbar">
        <p class="resultados-toolbar__texto">Mostrando <strong>{{$repuestos->count()}}</strong> resultados {{$criterio}}</p>
        <div class="resultados-toolbar__vistas" role="group" aria-label="Modo de visualización">
            <button type="button" id="btn-vista-imagenes" class="btn-vista is-active" onclick="buscar_imagenes()" title="Ver tabla compacta" aria-label="Ver tabla compacta" aria-pressed="true"><i class="fas fa-table" aria-hidden="true"></i></button>
            <button type="button" id="btn-vista-lista" class="btn-vista" onclick="buscar_info()" title="Ver tarjetas detalladas" aria-label="Ver tarjetas detalladas" aria-pressed="false"><i class="fas fa-list" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="row row_cero_margen resultados-layout" id="tabla_info_completa" style="display: none;">

        <div class="col-12" style="padding-right: 0px;padding-left:0px" id="rezultadoz">
            <fieldset id="rezultadoz_fieldset">
            <div class="tabla-scroll-y-300 h-100 resultados-tabla">
            <table id="tbl_repuestos" class="table table-sm table-hover" width="100%">
            <thead>
                @if($desde=='d' || $desde=='p' || $desde=='m')
                    <th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
                @endif
                @if($desde=='o')
                    <th width="5%" scope="col" class="letra_pequeña">Cod Int</th>
                    <th width="5%" scope="col" class="letra_pequeña">OEM</th>
                @endif
                @if($desde=='f')
                    <th width="8%" scope="col" class="letra_pequeña">Cod Fab</th>
                @endif
                <th width="7%" scope="col" class="letra_pequeña">Proveedor</th>
                <th width="9%" scope="col" class="letra_pequeña">Cod Rep Prov</th>
                <th width="18%" scope="col" class="letra_pequeña">Descripción</th>
                <th width="15%" scope="col" class="letra_pequeña">Medida</th>
                <th width="8%" scope="col" class="letra_pequeña">Marca</th>
                <th width="7%" scope="col" class="letra_pequeña">Origen</th>
            
                <th width="6%" scope="col" class="letra_pequeña">Precio Venta</th>
                <th width="1%" scope="col"></th> <!-- Agregar al carrito -->
                <th width="5%" scope="col" class="letra_pequeña">Stock total</th>
                <th width="8%" scope="col" class="letra_pequeña">Stock detallado</th>
                <th width="2%" scope="col" class="letra_pequeña">Stock Actualizado</th>
                <th width="5%" scope="col" class="letra_pequeña">Actualización de Precio</th>
                <th width="2%"></th> <!-- VER DETALLE -->
                <th width="1%"></th> <!-- MODIFICAR PRECIO -->
            </thead>
            <tbody>
            @foreach ($repuestos as $repuesto)

            @php
                // ocultar los repuestos que no tengan stock a los usuarios que tengan el rol vendedor
                // if(Auth::user()->rol->nombrerol === "vendedor"){
                //     if($repuesto->stock_actual<=0 && $repuesto->stock_actual_dos<=0 && $repuesto->stock_actual_tres<=0){
                //         continue;
                //     }
                // }
            @endphp
            @php
                //Fecha ultima actualización del precio del repuesto
                $firstDate = $repuesto->fecha_actualiza_precio;
                $secondDate = date('d-m-Y H:i:s');

                $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                $minutos = floor(abs($dateDifference / 60));
                $horas = floor($minutos / 60);
                $dias = floor($horas / 24);
            @endphp
            @if(\App\Services\StockPorLocalService::total($repuesto) > 0)
                <tr>
            @else
                <tr bgcolor="#ff9999">
            @endif
            <td class="resultado-imagen">
                @if(isset($fotos_por_repuesto[$repuesto->id]))
                    <a href="javascript:void(0)" class="resultado-imagen__enlace" onclick="abrir_foto_modal('{{$repuesto->id}}')" title="Ampliar imagen">
                        <img src="{{asset('storage/'.$fotos_por_repuesto[$repuesto->id])}}" alt="Imagen de {{$repuesto->descripcion}}" loading="lazy">
                    </a>
                @else
                    <div class="resultado-imagen__enlace resultado-imagen__vacia" aria-label="Repuesto sin imagen">
                        <i class="fas fa-image" aria-hidden="true"></i>
                        <span>Sin imagen</span>
                    </div>
                @endif
            </td>
            @if($desde=='d' || $desde=='p' || $desde=='m')
                <td class="letra_pequeña resultado-celda resultado-codigo">
                    @php $opt = false; @endphp
                    @foreach(Auth::user()->dame_permisos_mantenimiento() as $permiso)
                        @if($permiso->path_ruta == '/modificarRepuesto')
                            @php $opt = true; @endphp
                        @endif
                    @endforeach
                    @if(Auth::user()->rol->nombrerol === "Administrador" || $opt == true)
                        <a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank" style="color:black">{{$repuesto->codigo_interno}}</a>
                    @else
                        {{$repuesto->codigo_interno}}
                    @endif
                </td>
            @endif
            @if($desde=='o' )
                <td class="letra_pequeña resultado-celda resultado-codigo">{{$repuesto->codigo_interno}}</td>
                <td class="letra_pequeña resultado-celda resultado-codigo-secundario">{{$repuesto->codigo_oem}}</td>
            @endif
            @if($desde=='f' )
                <td class="letra_pequeña resultado-celda resultado-codigo">{{$repuesto->codigo_fab}}</td>
            @endif
            <td class="letra_pequeña resultado-celda resultado-proveedor">
                <span class="resultado-grupo-principal">{{$repuesto->empresa_nombre_corto}}</span>
                <span class="resultado-grupo-secundario"><small>Código</small><strong>{{$repuesto->cod_repuesto_proveedor}}</strong></span>
            </td>
            <td class="letra_pequeña resultado-celda resultado-descripcion">
                @if($repuesto->observaciones=='@@@')
                    {{$repuesto->descripcion}}
                @else
                    {{$repuesto->descripcion}} <i class="text-danger" style="font-weight: bold;">{{$repuesto->observaciones}}</i>
                @endif
            </td>
            @if($repuesto->medidas=='No Definidas' || $repuesto->medidas=='' || strlen($repuesto->medidas)==0  || $repuesto->medidas=='0')
                <td class="resultado-celda resultado-medida"></td>
            @else

            @php
            //$porciones =explode(',',$repuesto->medidas);
            // Usando explode con múltiples delimitadores
                $porciones = preg_split('/[:,]/', $repuesto->medidas);
                
                
            @endphp
                <td class="letra_pequeña resultado-celda resultado-medida" style="background-color:#eee;">
                    @foreach($porciones as $p)
                    @if($p == 'DISCO' || $p == 'PRENSA')
                        <span style="font-weight: bold">{{$p}}</span><br>
                    @else
                        <span>{{$p}}</span> <br>
                    @endif
                    
                    @endforeach
                </td>
            @endif
                <td class="letra_pequeña resultado-celda resultado-marca">
                    <span class="resultado-grupo-principal">{{strtoupper($repuesto->marcarepuesto)}}</span>
                    <span class="resultado-grupo-secundario"><small>Origen</small><strong>{{$repuesto->nombre_pais}}</strong></span>
                </td>
                
                <td class="letra_pequeña resultado-celda resultado-precio" style="text-align: right">
                    {{-- @if(Auth::user()->rol->nombrerol !== "Bodeguer@")               --}}
                    @if($repuesto->oferta == 1)
                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-danger">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                    @elseif($repuesto->oferta == 2)
                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})"><span class="badge badge-primary">Oferta</span></a>  {!!number_format($repuesto->precio_venta,0,',','.')!!}</p>
                    @else
                        @if(Auth::user()->rol->nombrerol == "Administrador")
                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                            <a style="color: black;" href="javascript:void(0)" onclick="modifikar_precio({{$repuesto->id}});">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                        </p>
                        @else
                        <p id="ppv-{!!$repuesto->id!!}" class="d-flex">
                            <a style="color: black;" href="javascript:void(0)">{!!number_format($repuesto->precio_venta,0,',','.')!!}</a> 
                        </p>
                        @endif
                    @endif
                

                    <input type="hidden" id="pv-{!!$repuesto->id!!}" value=" {!!number_format($repuesto->precio_venta,0,',','.')!!}">
                    {{-- @endif --}}
                </td>

                @if(\App\Services\StockPorLocalService::total($repuesto) > 0)
                    <td class="text-right letra_pequeña resultado-celda resultado-agregar"><button class="btn btn-primary btn-sm btn-agregar-carrito" onclick="agregar_carrito({{$repuesto->id}})"><i class="fas fa-cart-plus mr-1" aria-hidden="true"></i>Agregar</button></td>
                    <td class="letra_pequeña resultado-celda resultado-stock-total">
                            <input type="hidden" value="{{$repuesto->stock_actual}}" id="stock-{{$repuesto->id}}">
                            <input class="form-control form-control-sm letra_pequeña" style="text-align:center;padding:1px;height:20px" type="text" id="cant-{{$repuesto->id}}" onkeyup="enter_agregar_carrito(event,{{$repuesto->id}})" maxlength="3" inputmode="numeric" placeholder="Cantidad · stock {{\App\Services\StockPorLocalService::total($repuesto)}}" aria-label="Cantidad a agregar">
                            @if($repuesto->stock_minimo >= intval(\App\Services\StockPorLocalService::total($repuesto))) <span class="badge badge-danger"><a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="text-decoration-none text-white" data-target="#modalStockMinimo" data-toggle="modal">Stock Mínimo {{$repuesto->estado}} <br>@if(Auth::user()->id == 5 || Auth::user()->rol->nombrerol == "Administrador"){{$repuesto->fecha_modificacion_estado ? \Carbon\Carbon::parse($repuesto->fecha_modificacion_estado)->format('d-m-Y') : ''}}@endif</a> </span> @endif
                    </td>
                    <td class="letra_pequeña resultado-celda resultado-stock-detalle">
                            <select name="cboLocal" class="form-control form-control-sm letra_pequeña" id="local-{{$repuesto->id}}" style="padding:1px;height:20px">
                                @foreach($stocks_repuesto as $stock_repuesto)
                                
                                    @if(isset($stock_repuesto) && isset($repuesto))
                                            @if($stock_repuesto->id==$repuesto->id)
                                                <option value="{{$stock_repuesto->id_local}}">{{$stock_repuesto->local_nombre}}({{$stock_repuesto->stock_actual}}) - {{$stock_repuesto->responsable == "Bodega" ? 'Jael Rojas' : $stock_repuesto->responsable}}</option>
                                            @endif
                                    @endif
                                @endforeach
                            </select>
                    </td>
                @else
                    <td class="resultado-celda resultado-agregar"><button type="button" class="btn btn-secondary btn-sm" disabled><i class="fas fa-ban mr-1" aria-hidden="true"></i>Sin stock</button></td>
                    <td class="letra_pequeña resultado-celda resultado-stock-total"><span class="badge badge-danger"><a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="text-decoration-none text-white" data-target="#modalStockMinimo" data-toggle="modal">Sin Stock {{$repuesto->estado}}<br>@if(Auth::user()->rol->nombrerol == 'Administrador'){{$repuesto->fecha_modificacion_estado ? \Carbon\Carbon::parse($repuesto->fecha_modificacion_estado)->format('d-m-Y') : ''}}@endif </a></span>  </td>
                    <td class="resultado-celda resultado-stock-detalle"></td>
                @endif
            @php 
            $fechaStockActualizada = collect([
                $repuesto->fecha_actualizacion_stock,
                $repuesto->fecha_actualizacion_stock_dos,
                $repuesto->fecha_actualizacion_stock_tres,
            ])->filter()->max();

            $clase = "";
            if($repuesto->fecha_ultima === "2022-05-25"){
                $clase = "bg-danger text-white rounded"; 
            }
                    
            @endphp
                @if($fechaStockActualizada)
                <td class="letra_pequeña resultado-celda resultado-stock-fecha {{$clase}}"><strong>{{Carbon\Carbon::parse($fechaStockActualizada)->format('d-m-Y')}} </strong></td>
                @else 
                <td class="letra_pequeña resultado-celda resultado-stock-fecha {{$clase}}"><strong>---</strong></td>
                @endif
                @if ($dias <= 30 && Auth::user()->rol->nombrerol !== "Bodeguer@")
                    <td class="letra_pequeña resultado-celda resultado-dias" id="dias_precio-{{$repuesto->id}}" style="background: green; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
                @elseif($dias > 30 && $dias <= 60  && Auth::user()->rol->nombrerol !== "Bodeguer@")
                    <td class="letra_pequeña resultado-celda resultado-dias" id="dias_precio-{{$repuesto->id}}" style="background: yellow; text-align:center; border-radius: 5px;">{{$dias}}</td>
                @else
                @if(Auth::user()->rol->nombrerol !== "Bodeguer@")
                    <td class="letra_pequeña resultado-celda resultado-dias" id="dias_precio-{{$repuesto->id}}" style="background: red; text-align:center; color: white; border-radius: 5px;">{{$dias}}</td>
                    @endif
                @endif
                @if(in_array($repuesto->id,$hay_foto))
                    <td class="letra_pequeña resultado-detalle"><button class="btn btn-success btn-sm" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal" title="Ver detalle"><i class="fas fa-search" aria-hidden="true"></i></button></td>
                @else
                    <td class="letra_pequeña resultado-detalle"><button class="btn btn-success btn-sm" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal" title="Repuesto sin fotos"><i class="fas fa-search" aria-hidden="true"></i></button></td>
                @endif
                <td class="letra_pequeña resultado-modificar"><a href="javascript:void(0);" class="btn btn-warning btn-sm" onclick="modifikar_precio({{$repuesto->id}});" title="Modificar precio">M</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
            </div>
        </fieldset>
        </div>

        <aside id="modifikar_precio" style="display:none" aria-labelledby="editorPrecioTitulo">
            <div class="editor-precio__header">
                <h5 class="editor-precio__titulo" id="editorPrecioTitulo"><i class="fas fa-tag mr-2" aria-hidden="true"></i>Modificar precio</h5>
                <button type="button" class="editor-precio__cerrar" onclick="cerrar_panel_modifikar()" title="Cerrar editor" aria-label="Cerrar editor de precio"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="editor-precio__contenido">
                <p class="editor-precio__aviso"><strong>Importante:</strong> este cambio no actualiza los artículos que ya están agregados al carrito.</p>
                <label class="editor-precio__label" for="precio_a_modificar">Precio de venta</label>
                <div class="editor-precio__form">
                    <input type="text" value="0" id="precio_a_modificar" inputmode="numeric" aria-label="Nuevo precio de venta">
                    @if(Auth::user()->rol->nombrerol == "Administrador" || $permiso_modificar == true)
                        <button type="button" class="btn btn-success" onclick="guardar_nuevo_precio()" title="Guardar precio" aria-label="Guardar precio"><i class="fas fa-save"></i></button>
                    @endif
                    @if(Auth::user()->rol->nombrerol == "Administrador" || $permiso_actualizar == true)
                        <button type="button" class="btn btn-secondary" onclick="resetear_tiempo_precio()" title="Actualizar fecha del precio" aria-label="Actualizar fecha del precio"><i class="fas fa-sync-alt"></i></button>
                    @endif
                </div>
                <h6 class="editor-precio__compras-titulo"><i class="fas fa-history mr-2"></i>Historial de compras</h6>
                <div id="compras_repuesto"></div>
            </div>
        </aside>
    </div>

    @if(count($arreglo) > 0)
    <div id="tabla_info_imagenes" class="row row_cero_margen">
        <div class="col-12" style="padding-right:0;padding-left:0" id="rezultadoz_imagenes">
            <fieldset id="rezultadoz_fieldset_imagenes">
                <div class="tabla-scroll-y-300 h-100 resultados-tabla">
                    <table id="tbl_repuestos_imagenes" class="table table-sm table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>Código interno</th>
                                <th>Proveedor</th>
                                <th>Cod Rep Prov</th>
                                <th>Descripción</th>
                                <th>Medida</th>
                                <th>Marca</th>
                                <th>Origen</th>
                                <th>Precio venta</th>
                                <th>Acciones</th>
                                <th>Stock total</th>
                                <th>Stock detallado</th>
                                <th>Stock actualizado</th>
                                <th>Días precio</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($arreglo as $repuesto)
                            @php
                                $dateDifference = abs(time() - strtotime($repuesto->fecha_actualiza_precio));
                                $dias = floor($dateDifference / 86400);
                                $fechaStockActualizada = collect([
                                    $repuesto->fecha_actualizacion_stock,
                                    $repuesto->fecha_actualizacion_stock_dos,
                                    $repuesto->fecha_actualizacion_stock_tres,
                                ])->filter()->max();
                                $tieneStock = \App\Services\StockPorLocalService::total($repuesto) > 0;
                            @endphp
                            <tr @if(!$tieneStock) bgcolor="#ff9999" @endif>
                                <td class="letra_pequeña">
                                    @if(Auth::user()->rol->nombrerol === "Administrador" || $opt == true)
                                        <a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank" rel="noopener" style="color:black">{{$repuesto->codigo_interno}}</a>
                                    @else
                                        {{$repuesto->codigo_interno}}
                                    @endif
                                </td>
                                <td class="letra_pequeña">{{$repuesto->empresa_nombre_corto}}</td>
                                <td class="letra_pequeña">{{$repuesto->cod_repuesto_proveedor}}</td>
                                <td class="letra_pequeña">
                                    {{$repuesto->descripcion}}
                                    @if($repuesto->observaciones != '@@@' && trim($repuesto->observaciones ?? '') !== '')
                                        <i class="text-danger font-weight-bold">{{$repuesto->observaciones}}</i>
                                    @endif
                                </td>
                                <td class="letra_pequeña" style="background-color:#eee">
                                    @if(!in_array($repuesto->medidas, ['No Definidas', '', '0']) && strlen(trim($repuesto->medidas ?? '')) > 0)
                                        @foreach(preg_split('/[:,]/', $repuesto->medidas) as $medida)
                                            @if(in_array(trim($medida), ['DISCO', 'PRENSA']))
                                                <strong>{{trim($medida)}}</strong><br>
                                            @else
                                                <span>{{trim($medida)}}</span><br>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="letra_pequeña">{{strtoupper($repuesto->marcarepuesto)}}</td>
                                <td class="letra_pequeña">{{$repuesto->nombre_pais}}</td>
                                <td class="letra_pequeña precio-compacto">
                                    @if($repuesto->oferta == 1)
                                        <a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})" title="Ver detalle de la oferta" aria-label="Ver detalle de la oferta"><span class="badge badge-danger">Oferta</span></a>
                                    @elseif($repuesto->oferta == 2)
                                        <a href="javascript:void(0)" onclick="abrirModalPrecio({{$repuesto->id}})" title="Ver detalle de la oferta" aria-label="Ver detalle de la oferta"><span class="badge badge-primary">Oferta</span></a>
                                    @endif
                                    <span class="precio-compacto__valor" id="ppv-{{$repuesto->id}}">{{number_format($repuesto->precio_venta,0,',','.')}}</span>
                                    <input type="hidden" id="pv-{{$repuesto->id}}" value="{{number_format($repuesto->precio_venta,0,',','.')}}">
                                </td>
                                <td class="letra_pequeña">
                                    <div class="acciones-compactas">
                                        @if($tieneStock)
                                            <button class="btn btn-primary btn-sm" onclick="agregar_carrito({{$repuesto->id}},4)" title="Agregar al carrito"><i class="fas fa-cart-plus"></i></button>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled title="Sin stock"><i class="fas fa-ban"></i></button>
                                        @endif
                                        <button class="btn btn-success btn-sm" onclick="mas_detalle({{$repuesto->id}})" data-toggle="modal" data-target="#detalleModal" title="Ver detalle"><i class="fas fa-search"></i></button>
                                        <a href="javascript:void(0);" class="btn btn-warning btn-sm" onclick="modifikar_precio({{$repuesto->id}});" title="Modificar precio"><i class="fas fa-tag"></i></a>
                                    </div>
                                </td>
                                @if($tieneStock)
                                    <td class="letra_pequeña">
                                        <input type="hidden" value="{{$repuesto->stock_actual}}" id="stock-dos-{{$repuesto->id}}">
                                        <input class="form-control form-control-sm letra_pequeña" style="text-align:center" type="text" id="cant-dos-{{$repuesto->id}}" onkeyup="enter_cant_dos(event,{{$repuesto->id}})" maxlength="3" placeholder="Stk:{{\App\Services\StockPorLocalService::total($repuesto)}}">

                                    </td>
                                    <td class="letra_pequeña stock-detallado-compacto">
                                        <div class="stock-detallado-compacto__select">
                                        <select name="cboLocal" class="form-control form-control-sm letra_pequeña" id="local-dos-{{$repuesto->id}}">
                                            @foreach($stocks_repuesto as $stock_repuesto)
                                                @if(!empty($stock_repuesto) && $stock_repuesto->id == $repuesto->id)
                                                    <option value="{{$stock_repuesto->id_local}}">{{$stock_repuesto->local_nombre}} ({{$stock_repuesto->stock_actual}}) - {{$stock_repuesto->responsable == "Bodega" ? 'Jael Rojas' : $stock_repuesto->responsable}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        </div>
                                        @if($repuesto->stock_minimo >= intval(\App\Services\StockPorLocalService::total($repuesto)))
                                            <a href="javascript:void(0)" onclick="detalleStockMinimo({{$repuesto->id}})" class="badge badge-danger estado-stock-compacto text-white text-decoration-none" data-target="#modalStockMinimo" data-toggle="modal" title="Ver detalle del estado de stock">
                                                Stock Mínimo{{trim($repuesto->estado ?? '') !== '' ? ' '.$repuesto->estado : ''}}
                                                @if(Auth::user()->id == 5 || Auth::user()->rol->nombrerol == 'Administrador')
                                                    @if($repuesto->fecha_modificacion_estado)<br>{{\Carbon\Carbon::parse($repuesto->fecha_modificacion_estado)->format('d-m-Y')}}@endif
                                                @endif
                                            </a>
                                        @endif
                                    </td>
                                @else

                                    <td class="letra_pequeña">Sin stock</td>
                                    <td></td>
                                @endif
                                <td class="letra_pequeña"><strong>{{$fechaStockActualizada ? Carbon\Carbon::parse($fechaStockActualizada)->format('d-m-Y') : '---'}}</strong></td>
                                @if($dias <= 30)
                                    <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background:green;text-align:center;color:white">{{$dias}}</td>
                                @elseif($dias <= 60)
                                    <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background:yellow;text-align:center">{{$dias}}</td>
                                @else
                                    <td class="letra_pequeña" id="dias_precio-{{$repuesto->id}}" style="background:red;text-align:center;color:white">{{$dias}}</td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
    @endif
@else
    <div class="row row_cero_margen">
        <div class="col-12 alert alert-info">
            @if($criterio=='nadita')
                <h4><p style="color:#FF0000;text-align:center">Tu petición no cumple los criterios de búsqueda...</p></h4>
                <h5><strong>CRITERIOS:</strong></h5>
                <table border="1" class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope='col' class="d-flex justify-content-center">1 término</th>
                            <th scope='col' class="d-flex justify-content-center">2 términos</th>
                            <th scope='col' class="d-flex justify-content-center">3 términos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Código Interno (amo16)</td><td>familia familia (disco embrague)</td><td>fam fam marcaVeh (bomba agua fiat)</td></tr>
                        <tr><td>Código Fabricante (GWM-100A)</td><td>familia marcaVeh (amortiguador hyundai)</td><td>fam fam modeloVeh (bomba freno aveo)</td></tr>
                        <tr><td>Código Proveedor (001050095)</td><td>familia modeloVeh (alternador porter)</td><td>fam fam marcaRep (biela motor netmotors)</td></tr>
                        <tr><td>Código OEM (28113-A5800)</td><td>familia marcaRep (disco valeo)</td><td>fam marcaVeh marcaVeh (bujia land rover)</td></tr>
                        <tr><td>Medidas (140x14)</td><td>marcaVeh marcaVeh (mercedes benz)</td><td>fam marcaVeh modeloVeh (culata toyota dyna)</td></tr>
                        <tr><td>Descripción (MANILLA)</td><td>marcaVeh modeloVeh (hyundai accent)</td><td>fam marcaVeh marcaRep (tensor kia valeo)</td></tr>
                        <tr><td></td><td>modeloVeh modeloVeh (santa fe)</td><td>fam modeloVeh modeloVeh (rodamiento santa fe)</td></tr>
                        <tr><td></td><td>modeloVeh marcaRep (sorento netmotors)</td><td>fam modeloVeh marcaRep (disco canter valeo)</td></tr>
                        <tr><td></td><td></td><td>fam marcaRep marcaRep (amortiguador fabricas chinas)</td></tr>
                    </tbody>

                </table>
            @else
                <h4><p style="color:#FF0000";>No se encontraron repuestos.</p></h4>
            @endif
        </div>
    </div>
@endif
