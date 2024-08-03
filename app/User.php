<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
//Añadimos la clase JWTSubject 
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','rut','telefono','email', 'password','activo','image_path','role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function rol(){
            return $this->belongsTo('App\rol','role_id');
    }

    public function permisos()
    {
        return $this->belongsToMany('App\permisos','permissions_detail','usuarios_id','permission_id');
    }

    public function permisos_detalle(){
        return $this->hasMany('App\permissions_detail');
    }

    public function solicitud()
    {
        return $this->hasMany('App\solicitudes');
    }

    public function boleta()
    {
        return $this->hasMany('App\boleta');
    }

    public function dame_permisos(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    public function dame_permisos_venta(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',3)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_inventario(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',4)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_sii(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',5)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_mantenimiento(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',6)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_libros(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',7)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_reportes(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',8)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dame_permisos_busqueda_repuesto(){
        try {
            $permisos = User::select('users.name','permissions_detail.*')
                            ->where('permissions_detail.usuarios_id',Auth::user()->id)
                            ->where('permissions_detail.permission_id',9)
                            ->join('permissions_detail','permissions_detail.usuarios_id','users.id')
                            // ->distinct('permissions_detail.permission_id')
                            // ->groupBy('permissions_detail.permission_id')
                            ->get();
            return $permisos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function cajero()
    {
        return $this->hasOne('App\cajero', 'id_usuario');
    }
}
