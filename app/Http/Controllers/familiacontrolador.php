<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\familia;
use App\descuento;
use App\permissions_detail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Session;
use Illuminate\Validation\Rule;

class familiacontrolador extends Controller
{
    private $familias;


    public function confirmaringreso($path){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == $path){
                        return true;
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return true;
        } else {
            return false;
        }
    }

    private function validaSesion()
    {
        //Valida sesión: Revisar Handler.php en app\Exception, método render()
        // repuestos/Exceptions/Handler.php
        abort_if(Auth::user()->rol->nombrerol !== "Administrador", 403);
    }


    private function damefamilias()
    {
        $familias=familia::orderBy('nombrefamilia')->get();
        foreach($familias as $familia) $familia->nombrefamilia = strtoupper($familia->nombrefamilia);
        return $familias;
    }

    public function dame_familias()
    {
        //$this->validaSesion();
        return $this->damefamilias()->toJson();
    }

    public function dame_familias_con_descuento(){
        try {
            $descuentos = descuento::select('descuentos.*','familias.nombrefamilia')
                                    ->join('familias','familias.id','descuentos.id_familia')
                                    ->orderBy('id','desc')
                                    ->get();

            foreach ($descuentos as $d) {
                //Mostramos las familias en mayuscula.
                $d->nombrefamilia = strtoupper($d->nombrefamilia);
                // Cambiamos el formato de la fecha a dd-mm-YYYY usando la libreria carbon
                $d->desde = Carbon::parse($d->desde)->format('d-m-Y');
                $d->hasta = Carbon::parse($d->hasta)->format('d-m-Y');

            }

            $familias = $this->damefamilias()->toJson();

            $controlador_sii = new sii_controlador();
            $locales = $controlador_sii->damelocales();

            return [$familias,$descuentos,$locales];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       //$this->validaSesion();
        $confirma = $this->confirmaringreso('/familia');
        if($confirma){
            $familias=$this->damefamilias();
            return view('manten.familia',compact('familias'));
        }else return redirect('home');
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return "create";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $this->validaSesion();

        if(isset($request->btnGuardarFamilia))
        {
            $request->merge([
                'nombrefamilia' => trim((string) $request->nombrefamilia),
                'prefijo' => trim((string) $request->prefijo) !== ''
                    ? strtoupper(trim((string) $request->prefijo))
                    : null,
                'porcentaje' => $request->input('porcentaje', 0),
                'porcentaje_flete' => $request->input('porcentaje_flete', 0),
            ]);

            if($request->modificando==0){ //es nuevo
                $reglas=array(
                    'nombrefamilia'=>'required|max:50|unique:familias,nombrefamilia',
                    'porcentaje'=>'required|numeric|min:0|max:99',
                    'porcentaje_flete'=>'required|numeric|min:0|max:99',
                    'prefijo'=>['nullable', 'max:5', 'regex:/^[A-Z0-9\/]+$/', 'unique:familias,prefijo']
                );
            }else{
                $reglas=array(
                    'nombrefamilia'=>['required', 'max:50', Rule::unique('familias', 'nombrefamilia')->ignore($request->id_familia)],
                    'porcentaje'=>'required|numeric|min:0|max:99',
                    'porcentaje_flete'=>'required|numeric|min:0|max:99',
                    'prefijo'=>['nullable', 'max:5', 'regex:/^[A-Z0-9\/]+$/', Rule::unique('familias', 'prefijo')->ignore($request->id_familia)]
                );
            }


            $mensajes=array(
                'nombrefamilia.required'=>'Debe Ingresar un nombre de familia',
                'nombrefamilia.max'=>'El nombre de la familia debe tener como máximo 50 caracteres.',
                'nombrefamilia.unique'=>'El nombre de la familia ya existe.',
                'porcentaje.required'=>'Falta Ingresar el Porcentaje',
                'porcentaje.min'=>'El porcentaje no puede ser menor a 0.',
                'porcentaje.max'=>'El porcentaje debe ser menor a 100.',
                'porcentaje.numeric'=>'El porcentaje debe ser un número entero.',
                'porcentaje_flete.required'=>'Falta Ingresar el Porcentaje del Flete',
                'porcentaje_flete.min'=>'El porcentaje del Flete no puede ser menor a 0.',
                'porcentaje_flete.max'=>'El porcentaje del Flete debe ser menor a 100.',
                'porcentaje_flete.numeric'=>'El porcentaje del Flete debe ser un número entero.',
                'prefijo.required'=>'Debe ingresar el prefijo.',
                'prefijo.alpha_num'=>'Debe ingresar combinación de texto y números.',
                'prefijo.regex'=>'El prefijo solo puede contener letras, numeros y /.',
                'prefijo.max'=>'El prefijo debe tener hasta 5 caracteres.',
                'prefijo.unique'=>'El :attribute ya existe.'
            );

            $this->validate($request,$reglas,$mensajes);

            if($request->modificando==1){
                $familia=familia::find($request->id_familia);
                $msg="Familia Modificada: ";
            }else{
                $msg="Familia Guardada: ";
                $familia=new familia;
            }
            $familia->nombrefamilia=$request->nombrefamilia;
            $familia->porcentaje=$request->porcentaje;
            $familia->porcentaje_flete=$request->porcentaje_flete;
            $familia->prefijo=$request->prefijo;
            $familia->save();
            $familias=$this->damefamilias();
            if($request->donde=="familia")
                    return view('manten.familia',compact('familias'))->with('msgGuardado',$msg.$familia->nombrefamilia.' ('.$familia->id.')');
            if($request->donde=="factuprodu") return "OK";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return "show";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return "edit";
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return "update";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->validaSesion();

        familia::destroy($id);
        $familias=$this->damefamilias();
            return view('manten.familia',compact('familias'))->with('msgGuardado','Familia Eliminada.');
    }

    
}
