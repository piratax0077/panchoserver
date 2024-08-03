<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class permisos extends Model
{
    //
    protected $table = "permissions";
    protected $fillable = ['name'];
}
