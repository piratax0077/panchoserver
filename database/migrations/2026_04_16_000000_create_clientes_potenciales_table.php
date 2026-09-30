<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesPotencialesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes_potenciales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rut', 20);
            $table->string('nombre', 150);
            $table->string('telefono', 30)->nullable();
            $table->unsignedInteger('cotizacion_id')->nullable();
            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes_potenciales');
    }
}
