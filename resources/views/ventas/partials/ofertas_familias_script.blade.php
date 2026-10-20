(function () {
'use strict';
let familias=[], ofertas=[], hoy='', lote=null, cargando=false, guardando=false;
const canales={1:'Solo local',2:'Solo web',3:'Local + web',4:'Solo t\u00f3tem',5:'Local + t\u00f3tem',6:'Web + t\u00f3tem'};
const texto=v=>$('<span>').text(v==null?'':v).html().replace(/"/g,'&quot;').replace(/'/g,'&#39;');
const fecha=v=>v?String(v).slice(0,10):'';
const mostrarFecha=v=>v?fecha(v).split('-').reverse().join('-'):'Pendiente';
function errorAjax(xhr){const d=xhr.responseJSON||{};return d.errors?Object.values(d.errors).reduce((a,b)=>a.concat(b),[]).join(' '):xhr.status===403?'No tiene permiso para gestionar ofertas.':xhr.status===419?'Su sesi\u00f3n venci\u00f3. Recargue la p\u00e1gina.':'No se pudo completar la operaci\u00f3n. Intente nuevamente.';}
function estado(o){return fecha(o.desde)>hoy?'programada':fecha(o.hasta)<hoy?'caducada':'vigente';}
function filas(tipo){return Array.from(document.querySelectorAll('#familias_'+tipo+' tr[data-id]'));}
function elegidas(tipo){return filas(tipo).filter(f=>!f.hidden&&f.querySelector('input').checked).map(f=>Number(f.dataset.id));}
function seleccion(tipo){
 const visibles=filas(tipo).filter(f=>!f.hidden), ids=elegidas(tipo), catalogo=tipo==='catalogo';
 $(catalogo?'#familias_catalogo_cantidad':'#familias_seleccionadas').text(ids.length+' seleccionadas');
 $(catalogo?'#familias_crear':'#familias_editar').prop('disabled',!ids.length||guardando||cargando);
 const todas=document.getElementById('familias_'+(catalogo?'catalogo':'ofertas')+'_todas');
 todas.checked=visibles.length>0&&ids.length===visibles.length;todas.indeterminate=ids.length>0&&ids.length<visibles.length;
 filas(tipo).forEach(f=>f.classList.toggle('oferta-seleccionada',f.querySelector('input').checked));
 if(!catalogo)$('#familias_vacio').toggleClass('d-none',visibles.length>0);
}
function filtrar(tipo){
 const consulta=$(tipo==='catalogo'?'#buscar_familias_catalogo':'#buscar_familias_ofertas').val().trim().toLocaleLowerCase('es');
 const est=$('#familias_estado').val(), canal=$('#familias_canal').val();
 filas(tipo).forEach(f=>{f.hidden=!(f.dataset.nombre.toLocaleLowerCase('es').includes(consulta)&&(tipo==='catalogo'||((!est||est===f.dataset.estado)&&(!canal||canal===f.dataset.canal))));if(f.hidden)f.querySelector('input').checked=false;});
 seleccion(tipo);
}
function dibujar(){
 const catalogo=$('#familias_catalogo').empty(), tabla=$('#familias_ofertas').empty();
 const existentes=new Set(ofertas.map(o=>Number(o.id_familia)));
 familias.filter(f=>!existentes.has(Number(f.id))).forEach(f=>catalogo.append('<tr data-id="'+Number(f.id)+'" data-nombre="'+texto(f.nombrefamilia)+'"><td><input type="checkbox" aria-label="Seleccionar '+texto(f.nombrefamilia)+'"></td><td>'+texto(f.nombrefamilia)+'</td></tr>'));
 if(!catalogo.children().length)catalogo.append('<tr><td colspan="2" class="ofertas-empty">Todas las familias disponibles ya tienen oferta.</td></tr>');
 ofertas.forEach(o=>{
  const id=Number(o.id), est=estado(o);
  tabla.append('<tr data-id="'+id+'" data-nombre="'+texto(o.nombrefamilia)+'" data-estado="'+est+'" data-canal="'+texto(o.id_local)+'"><td><input type="checkbox" aria-label="Seleccionar '+texto(o.nombrefamilia)+'"></td><td>'+texto(o.nombrefamilia)+'</td><td>'+texto(o.porcentaje)+' %</td><td>'+mostrarFecha(o.desde)+'<br>'+mostrarFecha(o.hasta)+'</td><td>'+texto(canales[o.id_local]||'Sin canal')+'</td><td><span class="ofertas-status status-'+est+'">'+est.charAt(0).toUpperCase()+est.slice(1)+'</span></td><td><div class="ofertas-actions"><button type="button" class="btn btn-outline-primary btn-sm familia-editar" data-id="'+id+'">Editar</button><button type="button" class="btn btn-outline-secondary btn-sm familia-imagen" data-id="'+id+'">Imagen</button><button type="button" class="btn btn-outline-danger btn-sm familia-eliminar" data-id="'+id+'">Eliminar</button></div></td></tr>');
 });
 $('#familias_total').text(ofertas.length);filtrar('catalogo');filtrar('ofertas');
}
function cargar(){
 if(cargando)return;cargando=true;$('#familias_cargando').text('Cargando familias...');$('#familias_error').addClass('d-none');
 $('#familias_crear, #familias_editar, #familias_guardar').prop('disabled',true);
 $.getJSON('/ventas/ofertas/familias').done(d=>{
  familias=d.familias;ofertas=d.ofertas;hoy=d.hoy;lote=null;$('#familias_editor').addClass('d-none');dibujar();$('#familias_cargando').text('');
 }).fail(xhr=>{$('#familias_error').text(errorAjax(xhr)).removeClass('d-none');$('#familias_cargando').text('');}).always(()=>{cargando=false;seleccion('catalogo');seleccion('ofertas');$('#familias_guardar').prop('disabled',false);});
}
window.descuento_familia=function(){$('#modalOfertasFamilias').modal('show');cargar();};
window.mostrar_descuentos_familia=function(){cargar();};
window.familias_limpiar=function(){filas('ofertas').forEach(f=>f.querySelector('input').checked=false);seleccion('ofertas');};
function preparar(accion,items){
 if(guardando)return;
 if(items.length>500){$('#familias_error').text('Seleccione como m\u00e1ximo 500 familias.').removeClass('d-none');return;}
 lote={accion,items};$('#familias_desde, #familias_hasta, #familias_porcentaje').val('');$('#familias_local').val(accion==='crear'?'2':'');
 $('#familias_editor_titulo').text((accion==='crear'?'Crear':'Editar')+' '+items.length+' ofertas por familia');
 $('#familias_editor_ayuda').text(accion==='crear'?'Complete las fechas, canal y porcentaje para todas las familias elegidas.':'Complete solo lo que desea cambiar. Los campos vac\u00edos conservan los datos actuales.');
 $('#familias_editor_error').addClass('d-none');$('#familias_editor').removeClass('d-none');preview();document.getElementById('familias_editor').scrollIntoView({behavior:'smooth',block:'nearest'});
}
window.familias_abrir_lote=function(accion){const ids=new Set(elegidas(accion==='crear'?'catalogo':'ofertas'));const items=(accion==='crear'?familias:ofertas).filter(f=>ids.has(Number(f.id)));if(items.length)preparar(accion,items);};
window.familias_cancelar_lote=function(){if(guardando)return;lote=null;$('#familias_editor').addClass('d-none');};
function datos(){const d={accion:lote.accion,ids:lote.items.map(f=>Number(f.id))};[['desde','desde'],['hasta','hasta'],['local','id_local'],['porcentaje','porcentaje']].forEach(([input,campo])=>{const v=$('#familias_'+input).val();if(v!=='')d[campo]=v;});return d;}
function valores(item,d){
 const desde=d.desde||fecha(item.desde),hasta=d.hasta||fecha(item.hasta),porcentaje=d.porcentaje===undefined?item.porcentaje:d.porcentaje,local=d.id_local||item.id_local;
 let error='';if(desde&&hasta&&hasta<desde)error='La fecha final debe ser igual o posterior a la inicial.';
 if(d.porcentaje!==undefined&&(!Number.isFinite(Number(porcentaje))||Number(porcentaje)<=0||Number(porcentaje)>=100))error='El porcentaje debe ser mayor a 0 y menor a 100.';
 return{desde,hasta,porcentaje,local,error};
}
function preview(){if(!lote)return;const d=datos(), tabla=$('#familias_preview').empty();lote.items.forEach(item=>{const v=valores(item,d);tabla.append('<tr'+(v.error?' class="table-danger"':'')+'><td>'+texto(item.nombrefamilia)+'</td><td>'+texto(v.porcentaje===undefined?'Pendiente':v.porcentaje+' %')+'</td><td>'+mostrarFecha(v.desde)+'<br>'+mostrarFecha(v.hasta)+'</td><td>'+texto(canales[v.local]||'Pendiente')+'</td></tr>');});}
function validar(d){if(lote.accion==='crear'&&(!d.desde||!d.hasta||!d.id_local||d.porcentaje===undefined))return'Complete fechas, canal y porcentaje.';if(lote.accion==='editar'&&Object.keys(d).length===2)return'Indique al menos un dato para modificar.';for(const item of lote.items){const v=valores(item,d);if(v.error)return item.nombrefamilia+': '+v.error;}return'';}
window.familias_guardar_lote=function(){
 if(!lote||guardando||cargando)return;const d=datos(),error=validar(d);if(error){$('#familias_editor_error').text(error).removeClass('d-none');return;}
 $('#familias_editor_error').addClass('d-none');guardando=true;$('#familias_guardar, #familias_cancelar').prop('disabled',true);
 const cambios=[];if(d.desde)cambios.push('Desde: '+d.desde);if(d.hasta)cambios.push('Hasta: '+d.hasta);if(d.id_local)cambios.push('Canal: '+canales[d.id_local]);if(d.porcentaje!==undefined)cambios.push('Descuento: '+d.porcentaje+' %');
 Vue.swal({icon:'question',title:'Aplicar a '+d.ids.length+' familias',text:cambios.join(' / '),showCancelButton:true,confirmButtonText:'Aplicar',cancelButtonText:'Cancelar'}).then(res=>{
  if(!(res.isConfirmed||res.value===true)){terminar();return;}
  $('#familias_guardar').text('Guardando...');
  $.ajax({type:'POST',url:'/ventas/ofertas/familias/lote',data:d}).done(resp=>{terminar();cargar();Vue.swal({icon:'success',text:resp.mensaje});}).fail(xhr=>$('#familias_editor_error').text(errorAjax(xhr)).removeClass('d-none')).always(terminar);
 }).catch(terminar);
};
function terminar(){guardando=false;$('#familias_guardar, #familias_cancelar').prop('disabled',false);$('#familias_guardar').text('Aplicar cambios');}
$(function(){
 $('#buscar_familias_catalogo').on('input',()=>filtrar('catalogo'));$('#buscar_familias_ofertas').on('input',()=>filtrar('ofertas'));$('#familias_estado, #familias_canal').on('change',()=>filtrar('ofertas'));
 ['catalogo','ofertas'].forEach(tipo=>{
  $('#familias_'+tipo).on('change','input[type=checkbox]',()=>seleccion(tipo));
  $('#familias_'+tipo+'_todas').on('change',function(){filas(tipo).filter(f=>!f.hidden).forEach(f=>f.querySelector('input').checked=this.checked);seleccion(tipo);});
 });
 $('#familias_ofertas').on('click','.familia-editar',function(){const oferta=ofertas.find(o=>Number(o.id)===Number(this.dataset.id));if(oferta)preparar('editar',[oferta]);});
 $('#familias_ofertas').on('click','.familia-imagen',function(){const id=Number(this.dataset.id);$('#modalOfertasFamilias').one('hidden.bs.modal',()=>editar_descuento_familia(id)).modal('hide');});
 $('#modal_editar_descuento_familia').on('hidden.bs.modal',()=>{$('#modalOfertasFamilias').modal('show');cargar();});
 $('#familias_ofertas').on('click','.familia-eliminar',function(){const id=Number(this.dataset.id);Vue.swal({icon:'warning',title:'Eliminar oferta de familia',text:'Se quitar\u00e1 el descuento de esta familia.',showCancelButton:true,confirmButtonText:'Eliminar',cancelButtonText:'Cancelar'}).then(res=>{if(res.isConfirmed||res.value===true)eliminar_descuento(id);});});
 $('#familias_desde, #familias_hasta, #familias_local, #familias_porcentaje').on('input change',preview);
 $('#modalOfertasFamilias').on('hide.bs.modal',e=>{if(guardando)e.preventDefault();});
});
})();
