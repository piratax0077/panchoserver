<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class metas extends Model
{
    use HasFactory;
    protected $table = 'metas';

    protected $fillable=[
        'mes',
        'año',
        'meta',
        'activo',
        'usuarios_id',
    ];
}
