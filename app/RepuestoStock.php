<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class RepuestoStock extends Model
{
    protected $table = 'repuesto_stocks';
    protected $guarded = ['id'];
    protected $casts = ['cantidad' => 'integer', 'local_id' => 'integer'];

    public function local() { return $this->belongsTo(local::class, 'local_id'); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
}
