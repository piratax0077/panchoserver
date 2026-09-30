<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdProveedorToImportacionesExcelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('importaciones_excel', function (Blueprint $table) {
            $table->unsignedBigInteger('id_proveedor')->nullable()->after('usuarios_id')->comment('ID del proveedor de donde viene el Excel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('importaciones_excel', function (Blueprint $table) {
            $table->dropColumn('id_proveedor');
        });
    }
}
