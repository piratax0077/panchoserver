<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRepuestoStocksTable extends Migration
{
    public function up()
    {
        Schema::create('repuesto_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedMediumInteger('repuesto_id');
            $table->unsignedSmallInteger('local_id');
            $table->unsignedInteger('cantidad')->default(0);
            $table->string('ubicacion_fisica', 255)->nullable();
            $table->unsignedInteger('responsable_id')->nullable();
            $table->timestamps();
            $table->unique(['repuesto_id', 'local_id']);
            $table->foreign('repuesto_id')->references('id')->on('repuestos');
            $table->foreign('local_id')->references('id')->on('locales');
        });
        Schema::table('stock_movimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('repuesto_stock_id')->nullable()->index();
            $table->foreign('repuesto_stock_id')->references('id')->on('repuesto_stocks');
        });
    }

    public function down()
    {
        if (DB::table('repuesto_stocks')->exists()) {
            throw new \RuntimeException('Hay inventario migrado. Reconcilie y respalde los datos antes de revertir.');
        }
        Schema::table('stock_movimientos', function (Blueprint $table) {
            $table->dropForeign(['repuesto_stock_id']);
            $table->dropColumn('repuesto_stock_id');
        });
        Schema::dropIfExists('repuesto_stocks');
    }
}
