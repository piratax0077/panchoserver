<?php
// Regularizacion puntual autorizada por el usuario. Sin --apply solo consulta.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$aplicar = in_array('--apply', $argv, true);
$esperados = [99479 => 40000, 99489 => 73000];
$resultado = DB::transaction(function () use ($aplicar, $esperados) {
    $efectivo = DB::table('formapago')->where('id', 1)->where('activo', 1)->first();
    if (!$efectivo || mb_strtolower(trim($efectivo->formapago)) !== 'efectivo') {
        throw new RuntimeException('La forma de pago 1 no corresponde a efectivo activo.');
    }
    $documentos = [];
    $previos = [];
    foreach ($esperados as $folio => $monto) {
        $coincidencias = DB::table('boletas')->where('num_boleta', $folio)->lockForUpdate()->get();
        if ($coincidencias->count() !== 1) throw new RuntimeException('Folio inexistente o duplicado: '.$folio);
        $boleta = $coincidencias->first();
        if ((float) $boleta->total !== (float) $monto || substr($boleta->fecha_emision,0,10) !== '2026-10-14'
            || !$boleta->activo || $boleta->es_credito || $boleta->es_delivery
            || $boleta->estado_sii !== 'ACEPTADO') {
            throw new RuntimeException('La boleta no cumple las condiciones verificadas: '.$folio);
        }
        $documentos[$folio] = $boleta;
        $previos[$folio] = DB::table('pagos')->where('tipo_doc','bo')->where('id_doc',$boleta->id)->lockForUpdate()->get();
    }
    $respaldo = null;
    if ($aplicar) {
        $directorio = storage_path('app/auditoria');
        if (!is_dir($directorio) && !mkdir($directorio,0770,true)) throw new RuntimeException('No se pudo crear directorio de respaldo.');
        $respaldo = $directorio.'/pagos_totem_20261014_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.json';
        $datos = ['motivo'=>'Registro en efectivo autorizado para pagos omitidos por fallo de detalle del totem',
            'documentos_antes'=>$documentos,'pagos_antes'=>$previos];
        if (file_put_contents($respaldo,json_encode($datos,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX) === false) {
            throw new RuntimeException('No se pudo respaldar el estado previo.');
        }
    }
    $salida = [];
    foreach ($documentos as $folio => $boleta) {
        $activos = $previos[$folio]->where('activo',1);
        if ($activos->count() > 0) {
            $salida[] = ['folio'=>$folio,'accion'=>'omitido: ya tiene pago activo','monto_activo'=>$activos->sum('monto')];
            continue;
        }
        $datos = ['tipo_doc'=>'bo','id_doc'=>$boleta->id,'id_cliente'=>$boleta->id_cliente,
            'id_forma_pago'=>1,'referencia_pago'=>0,'fecha_pago'=>'2026-10-14',
            'monto'=>$esperados[$folio], 'referencia'=>'Regularizacion efectivo autorizada: fallo detalle totem, boleta '.$folio,
            'activo'=>1,'usuarios_id'=>$boleta->usuarios_id,'created_at'=>now(),'updated_at'=>now()];
        $id = $aplicar ? DB::table('pagos')->insertGetId($datos) : null;
        $salida[] = ['folio'=>$folio,'accion'=>$aplicar?'pago efectivo registrado':'pago efectivo pendiente',
            'monto'=>$esperados[$folio],'pago_id'=>$id,'fecha_pago'=>'2026-10-14'];
    }
    if ($aplicar) {
        // El archivo anterior conserva la cabecera y pagos previos; el resultado va aparte.
        if (file_put_contents($respaldo.'.resultado.json',json_encode($salida,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar la auditoria de resultados.');
        }
    }
    return ['aplicado'=>$aplicar,'respaldo'=>$respaldo,'pagos'=>$salida];
});
echo json_encode($resultado,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),PHP_EOL;