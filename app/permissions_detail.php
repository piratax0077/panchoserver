<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class permissions_detail extends Model
{
    protected $table = 'permissions_detail';
    protected $fillable = [
        'permission_id','descripcion','path_ruta','usuarios_id','created_at','updated_at'];

    public function user(){
        return $this->belongsTo('App\user','usuarios_id');
    }
}
