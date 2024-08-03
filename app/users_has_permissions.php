<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class users_has_permissions extends Model
{
    //
    protected $table='users_has_permissions';
    public $timestamps = false;
    protected $fillable=[
        'user_id',
        'permission_id'
    ];

}
