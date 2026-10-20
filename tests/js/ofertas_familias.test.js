const assert=require('assert'),fs=require('fs'),vm=require('vm');
const fields=new Map();let ready;
function get(key){if(!fields.has(key))fields.set(key,{value:'',text:'',props:{},handlers:{}});return fields.get(key);}
const rows=[{hidden:false,dataset:{id:'1',nombre:'Aceites',estado:'vigente',canal:'2'},checkbox:{checked:false}},{hidden:false,dataset:{id:'2',nombre:'Baterias',estado:'programada',canal:'1'},checkbox:{checked:false}}];
rows.forEach(r=>{r.querySelector=()=>r.checkbox;r.classList={toggle(){}};});
const catalogo=[{hidden:false,dataset:{id:'3',nombre:'Filtros'},checkbox:{checked:false}}];catalogo.forEach(r=>{r.querySelector=()=>r.checkbox;r.classList={toggle(){}};});
const selects={};const document={querySelectorAll(s){return s.includes('catalogo')?catalogo:rows;},getElementById(id){if(!selects[id])selects[id]={};return selects[id];}};
function $(key){if(typeof key==='function'){ready=key;return;}const f=get(key);const api={val(){return f.value;},text(v){f.text=v;return api;},prop(k,v){f.props[k]=v;return api;},toggleClass(){return api;},on(e,d,cb){f.handlers[e]=cb||d;return api;}};return api;}
const context={window:{},document,$,console,Map,Set,Number};vm.createContext(context);
let script=fs.readFileSync('resources/views/ventas/partials/ofertas_familias_script.blade.php','utf8');const end=script.lastIndexOf('})();');
script=script.slice(0,end)+'window.familyTest={valores,validar,filtrar,setLote(v){lote=v;}};'+script.slice(end);vm.runInContext(script,context);ready();
rows[0].checkbox.checked=true;rows[1].checkbox.checked=true;get('#familias_ofertas').handlers.change();
assert.equal(get('#familias_seleccionadas').text,'2 seleccionadas');assert.equal(get('#familias_editar').props.disabled,false);
get('#buscar_familias_ofertas').value='aceites';get('#familias_estado').value='';get('#familias_canal').value='';context.window.familyTest.filtrar('ofertas');
assert.equal(rows[1].hidden,true);assert.equal(rows[1].checkbox.checked,false);assert.equal(get('#familias_seleccionadas').text,'1 seleccionadas');
get('#familias_ofertas_todas').handlers.change.call({checked:false});assert.equal(get('#familias_seleccionadas').text,'0 seleccionadas');assert.equal(get('#familias_editar').props.disabled,true);
catalogo[0].checkbox.checked=true;get('#familias_catalogo').handlers.change();assert.equal(get('#familias_catalogo_cantidad').text,'1 seleccionadas');assert.equal(get('#familias_crear').props.disabled,false);
const item={id:1,nombrefamilia:'Aceites',desde:'2026-10-01',hasta:'2026-10-31',porcentaje:12.5,id_local:6};const api=context.window.familyTest;
api.setLote({accion:'editar',items:[item]});const result=api.valores(item,{id_local:2});assert.equal(result.porcentaje,12.5);assert.equal(result.desde,item.desde);assert.equal(result.local,2);assert.equal(result.error,'');
assert.notEqual(api.valores(item,{desde:'2026-11-01'}).error,'');assert.notEqual(api.valores(item,{porcentaje:100}).error,'');assert.notEqual(api.validar({accion:'editar',ids:[1]}),'');
assert.equal(api.validar({accion:'editar',ids:[1],porcentaje:10}),'');
api.setLote({accion:'crear',items:[{id:3,nombrefamilia:'Filtros'}]});assert.notEqual(api.validar({accion:'crear',ids:[3],porcentaje:10}),'');
console.log('PASS: family checkbox events, counts, visible selections, partial previews, percentages and dates.');
