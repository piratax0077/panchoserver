<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClonacionRvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clonacion_rv', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_repuesto_origen');
            $table->unsignedBigInteger('id_repuesto_destino');
            $table->date('fecha_emision');
            $table->timestamps();

            $table->foreign('id_repuesto_origen')->references('id')->on('repuestos');
            $table->foreign('id_repuesto_destino')->references('id')->on('repuestos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clonacion_rv');
    }
}
