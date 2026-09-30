<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class abono extends Model
{
    protected $table='abono';
    protected $fillable=[
        'num_abono',
        'usuarios_id',
        'fecha_emision',
        'nombre_cliente',
        'telefono',
        'email',
        'abono',
        'patente',
        'saldo_pendiente',
        'precio_lista',
        'forma_pago',
        'por_encargo',
        'por_cobrar',
        'url_pdf',
        'id_responsable',
        'activo',
    ];



    public function user(){
        return $this->hasMany('App\User');
    }

}
