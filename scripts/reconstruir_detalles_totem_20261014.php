<?php
// Recuperacion puntual. Sin --apply solo muestra la correspondencia comprobada.
require dirname(__DIR__).'/vendor/autoload.php';
$app=require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Services\ReconstruccionDetalleBoleta;

$aplicar=in_array('--apply',$argv,true);
$resultado=DB::transaction(function()use($aplicar){
    $fuentes=[99478=>[137735,137736],99479=>[137735,137736],99489=>[137751]];
    $planes=[]; $respaldo=[];
    foreach($fuentes as $folio=>$ids){
        $boletas=DB::table('boletas')->where('num_boleta',$folio)->lockForUpdate()->get();
        if($boletas->count()!==1)throw new RuntimeException('Folio inexistente o duplicado.');
        $b=$boletas->first();
        if(substr($b->fecha_emision,0,10)!=='2026-10-14' || !$b->activo || $b->estado_sii!=='ACEPTADO')throw new RuntimeException('Documento no esperado.');
        $carrito=DB::table('carrito_transferido')->join('repuestos','repuestos.id','=','carrito_transferido.id_repuestos')
            ->whereIn('carrito_transferido.id',$ids)->orderBy('carrito_transferido.item')->orderBy('carrito_transferido.id')
            ->select('carrito_transferido.*','repuestos.descripcion','repuestos.codigo_interno')->lockForUpdate()->get();
        if($carrito->count()!==count($ids))throw new RuntimeException('Falta el carrito historico.');
        foreach($carrito as $c){
            if($c->usuarios_id!=5 || $c->cajeros_id!=407 || strpos($c->titulo,'TOTEM ')!==0
                || $c->created_at>$b->created_at || $c->updated_at<$b->created_at)throw new RuntimeException('Carrito fuera del contexto temporal esperado.');
        }
        $path=base_path('xml/generados/boletas/'.basename($b->url_xml));
        $xml=simplexml_load_file($path);
        if(!$xml)throw new RuntimeException('XML no disponible.');
        $header=$xml->xpath('//*[local-name()="Encabezado"]')[0];
        if((string)$header->IdDoc->Folio!==(string)$folio || (float)$header->Totales->MntTotal!==(float)$b->total)throw new RuntimeException('XML no corresponde al documento.');
        $previos=DB::table('boletas_detalle')->where('id_boleta',$b->id)->lockForUpdate()->get();
        $plan=ReconstruccionDetalleBoleta::plan($carrito,$previos,$xml);
        if(abs(array_sum(array_column(array_column($plan,'datos'),'total'))-(float)$b->total)>0.01)throw new RuntimeException('Detalle no cuadra con total de boleta.');
        $notas=DB::table('notas_de_credito')->where('docum_referencia','like','bo*'.$folio.'*%')->get(['id','num_nota_credito','total','estado_sii']);
        $planes[$folio]=['boleta'=>$b,'plan'=>$plan,'notas_credito'=>$notas];
        $respaldo[$folio]=['boleta'=>$b,'carrito'=>$carrito,'detalles_antes'=>$previos,'notas_credito'=>$notas,
            'pagos_antes'=>DB::table('pagos')->where('tipo_doc','bo')->where('id_doc',$b->id)->get(),
            'stock_antes'=>DB::table('repuestos')->whereIn('id',$carrito->pluck('id_repuestos'))->get(['id','stock_actual','stock_actual_dos','stock_actual_tres']),
            'xml_sha256'=>hash_file('sha256',$path)];
    }
    $archivo=null;
    if($aplicar){
        $dir=storage_path('app/auditoria');
        if(!is_dir($dir)&&!mkdir($dir,0770,true))throw new RuntimeException('No se pudo crear auditoria.');
        $archivo=$dir.'/detalles_totem_20261014_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.json';
        if(file_put_contents($archivo,json_encode($respaldo,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX)===false)throw new RuntimeException('No se pudo respaldar.');
    }
    $salida=[];
    foreach($planes as $folio=>$p){
        $b=$p['boleta'];$filas=[];
        foreach($p['plan'] as $v){
            $id=$v['detalle_existente_id'];
            if($id===null&&$aplicar){
                $datos=$v['datos']+['id_boleta'=>$b->id,'pu_neto'=>round($v['datos']['precio_venta']/1.19,2),
                    'activo'=>1,'usuarios_id'=>$b->usuarios_id,'created_at'=>now(),'updated_at'=>now()];
                $id=DB::table('boletas_detalle')->insertGetId($datos);
            }
            $filas[]=['carrito_transferido_id'=>$v['carrito_transferido_id'],'linea_xml'=>$v['linea_xml'],
                'detalle_id'=>$id,'accion'=>$v['detalle_existente_id']!==null?'existente':($aplicar?'insertado':'faltante'),
                'repuesto_id'=>$v['datos']['id_repuestos'],'cantidad'=>$v['datos']['cantidad'],'total'=>$v['datos']['total']];
        }
        if($aplicar){
            $actual=DB::table('boletas_detalle')->where('id_boleta',$b->id);
            if($actual->count()!==count($p['plan']) || abs((float)$actual->sum('total')-(float)$b->total)>0.01)throw new RuntimeException('Fallo validacion posterior.');
            $stockDespues=DB::table('repuestos')->whereIn('id',array_column(array_column($p['plan'],'datos'),'id_repuestos'))->get(['id','stock_actual','stock_actual_dos','stock_actual_tres']);
            if($stockDespues->toJson()!==$respaldo[$folio]['stock_antes']->toJson())throw new RuntimeException('Cambio inesperado de stock.');
        }
        $salida[]=['folio'=>$folio,'detalles'=>$filas,'notas_credito'=>$p['notas_credito']];
    }
    if($aplicar&&file_put_contents($archivo.'.resultado.json',json_encode($salida,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX)===false)throw new RuntimeException('No se pudo guardar resultado.');
    return ['aplicado'=>$aplicar,'respaldo'=>$archivo,'boletas'=>$salida];
});
echo json_encode($resultado,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),PHP_EOL;