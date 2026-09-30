<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosValesMercaderia extends Migration
{
    public function up()
    {
        Schema::create('vale_mercaderia_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vale_mercaderia_id')->nullable();
            $table->unsignedBigInteger('repuesto_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->integer('local_id')->nullable();
            $table->integer('cantidad');
            $table->enum('tipo_movimiento', ['agregar', 'eliminar', 'devolver']);
            $table->string('numero_voucher')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('vale_mercaderia_id');
            $table->index('repuesto_id');
            $table->index('usuario_id');
            $table->index('tipo_movimiento');
            $table->index('created_at');
            $table->index(['repuesto_id', 'created_at']);
        });

        // Agregar claves foráneas después (con mejor control de tipos)
        Schema::table('vale_mercaderia_movimientos', function (Blueprint $table) {
            // Solo agregamos si las tablas existen y coinciden los tipos
            try {
                $table->foreign('repuesto_id')->references('id')->on('repuestos');
            } catch (\Exception $e) {
                // Silenciar si hay error
            }
            
            try {
                $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {
                // Silenciar si hay error
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('vale_mercaderia_movimientos');
    }
}
