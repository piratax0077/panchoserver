const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const fields = new Map();
let ready;
const get = key => { if (!fields.has(key)) fields.set(key,{value:'',text:'',props:{},handlers:{}}); return fields.get(key); };
const rows = [
 {hidden:false,dataset:{codigo:'ABC',descripcion:'Filtro de aceite',usuario:'Ana',estado:'vigente',local:'2'},checkbox:{checked:true}},
 {hidden:false,dataset:{codigo:'DEF',descripcion:'Freno',usuario:'Luis',estado:'programada',local:'1'},checkbox:{checked:true}},
];
rows.forEach(row => { row.querySelector=()=>row.checkbox; row.classList={toggle(){}}; });
const all={checked:false,indeterminate:false};
const document={querySelectorAll(){return rows;},getElementById(){return all;}};
function $(selector) {
 if (typeof selector === 'function') { ready=selector; return; }
 const field=get(selector);
 const api={
   val(){return field.value;},text(value){field.text=value;return api;},
   prop(key,value){field.props[key]=value;return api;},toggleClass(){return api;},
   empty(){return api;},append(){return api;},each(){return api;},attr(){return 'test-token';},
   on(event, delegate, callback){field.handlers[event]=callback || delegate;return api;},ajaxError(){return api;}
 };
 return api;
}
$.ajaxSetup=function () {};
const context={window:{},document,$,console,Map,Number};
vm.createContext(context);
let script=fs.readFileSync('resources/views/ventas/partials/ofertas_script.blade.php','utf8');
const end=script.lastIndexOf('})();');
script=script.slice(0,end)+'window.testOffers={calcularPreview,validarLote,filtrar,setLote(value){lote=value;}};'+script.slice(end);
vm.runInContext(script,context);
const api=context.window.testOffers;
api.setLote({accion:'editar',items:[{id:1,codigo:'ABC',precio:19990,actualizado:18000,desde:'2026-10-01',hasta:'2026-10-31',local:'2'}]});
const item={id:1,codigo:'ABC',precio:19990,actualizado:18000,desde:'2026-10-01',hasta:'2026-10-31',local:'2'};
let result=api.calcularPreview(item,{local:'6'});
assert.equal(result.actualizado,18000);
assert.equal(result.desde,'2026-10-01');
assert.equal(result.local,'6');
result=api.calcularPreview(item,{tipo_descuento:'porcentaje',valor_descuento:'12.5'});
assert.equal(result.actualizado,17491);
assert.equal(result.error,'');
assert.notEqual(api.calcularPreview(item,{tipo_descuento:'monto',valor_descuento:'20000'}).error,'');
assert.notEqual(api.calcularPreview(item,{desde:'2026-11-01'}).error,'');
assert.notEqual(api.validarLote({accion:'editar',ids:[1]}),'');
get('#filtro_ofertas').value='aceite';
get('#filtro_estado').value='';get('#filtro_canal').value='';
api.filtrar();
assert.equal(rows[0].hidden,false);
assert.equal(rows[1].hidden,true);
assert.equal(rows[1].checkbox.checked,false,'Hidden offers must be deselected');
assert.equal(get('#cantidad_seleccionadas').text,'1 seleccionadas');
assert.equal(all.checked,true);
get('#filtro_estado').value='programada';
api.filtrar();
assert.equal(rows[0].checkbox.checked,false);
assert.equal(get('#btn_editar_lote').props.disabled,true);
console.log('PASS: preview preserves untouched fields, discounts use original price, invalid prices/dates are rejected and filters deselect hidden offers.');

get('#filtro_ofertas').value=''; get('#filtro_estado').value=''; get('#filtro_canal').value='';
ready();
rows[0].checkbox.checked=true; rows[1].checkbox.checked=true;
get('#tbody_nuevas_ofertas').handlers.change();
assert.equal(get('#cantidad_seleccionadas').text,'2 seleccionadas');
assert.equal(get('#btn_editar_lote').props.disabled,false);
rows[0].checkbox.checked=false;
get('#tbody_nuevas_ofertas').handlers.change();
assert.equal(get('#cantidad_seleccionadas').text,'1 seleccionadas');
get('#seleccionar_visibles').handlers.change.call({checked:false});
assert.equal(get('#cantidad_seleccionadas').text,'0 seleccionadas');
assert.equal(get('#btn_editar_lote').props.disabled,true);
console.log('PASS: actual initialization binds checkbox events and updates the selected count and bulk-edit button.');
