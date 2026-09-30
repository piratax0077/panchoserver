<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportacionesExcelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('importaciones_excel', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo')->comment('Nombre del archivo Excel subido');
            $table->string('tipo_importacion')->comment('Tipo de importación: stock_minimo, oems, etc');
            $table->integer('total_filas')->default(0)->comment('Total de filas en el archivo');
            $table->integer('filas_procesadas')->default(0)->comment('Filas procesadas correctamente');
            $table->integer('total_errores')->default(0)->comment('Total de errores encontrados');
            $table->string('estado')->default('OK')->comment('Estado: OK, ERROR, PARCIAL');
            $table->json('errores_json')->nullable()->comment('Detalles de errores en formato JSON');
            $table->unsignedBigInteger('usuarios_id')->nullable()->comment('ID del usuario que realizó la importación');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('importaciones_excel');
    }
}
