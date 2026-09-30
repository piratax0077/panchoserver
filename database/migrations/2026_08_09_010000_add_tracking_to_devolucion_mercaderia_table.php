<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTrackingToDevolucionMercaderiaTable extends Migration
{
    public function up()
    {
        Schema::table('devolucion_mercaderia', function (Blueprint $table) {
            $table->string('estado', 20)->nullable()->after('activo')->index();
            $table->unsignedBigInteger('procesado_por')->nullable()->after('estado');
            $table->timestamp('procesado_at')->nullable()->after('procesado_por');
        });

        DB::table('devolucion_mercaderia')->where('activo', 1)->update(['estado' => 'PENDIENTE']);
        DB::table('devolucion_mercaderia')->where('activo', 0)->update(['estado' => 'PROCESADA']);
    }

    public function down()
    {
        Schema::table('devolucion_mercaderia', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn(['estado', 'procesado_por', 'procesado_at']);
        });
    }
}
