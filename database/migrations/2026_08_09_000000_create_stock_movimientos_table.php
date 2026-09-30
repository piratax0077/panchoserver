<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovimientosTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedMediumInteger('repuesto_id');
            $table->unsignedSmallInteger('local_id')->nullable();
            $table->unsignedTinyInteger('ubicacion_indice');
            $table->string('campo_stock', 32);
            $table->integer('stock_anterior');
            $table->integer('cantidad_movimiento');
            $table->integer('stock_nuevo');
            $table->string('tipo', 40)->default('AJUSTE_MANUAL');
            $table->text('motivo');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->unsignedInteger('responsable_id')->nullable();
            $table->string('referencia_tipo', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->uuid('grupo_uuid')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('repuesto_id')->references('id')->on('repuestos');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->index(['repuesto_id', 'ubicacion_indice', 'created_at'], 'stock_mov_rep_ubi_fecha_idx');
            $table->index(['usuario_id', 'created_at'], 'stock_mov_usuario_fecha_idx');
            $table->index(['referencia_tipo', 'referencia_id'], 'stock_mov_referencia_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movimientos');
    }
}
