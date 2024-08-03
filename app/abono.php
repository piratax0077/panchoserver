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
        'saldo_pendiente',
        'precio_lista',
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
