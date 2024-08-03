<?php

namespace App\Http\Controllers;

//use Debugbar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\parametro;
use Session;
//*** PARA IMPRESION CON MIKE42 *** https://parzibyte.me/blog/2017/09/10/imprimir-ticket-en-impresora-termica-php/

/*
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
*/
class logincontrolador extends Controller
{
    private function cargar_parametros_sistema($id_usuario)
    {
        Session::put('usuario_id',$id_usuario); //Aquí cambiarlo por el ID del usuario que se ha loguedo.
        //Session::put('usuario',$request->nombreUsuario);
        Session::put('local',1); // tabla locales id = 1
        Session::put('acceso','SI');
        $parametros=parametro::select('codigo','valor')->get();
        foreach($parametros as $p)
        {
            Session::put($p->codigo,$p->valor);
        }
      //  Debugbar::addMessage('LOGIN: IVA: '.Session::get('PARAM_IVA'),'depurador');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Session::get('acceso')=='SI')
        {
            return view('bienvenida');
            //return redirect()->route('documentos.index');
        }else{
            return view('login');
        }

        //return view('login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Session::forget('usuario_id');
        Session::forget('usuario');
        Session::forget('acceso');
        return view('login')->with('msgGuardado','Vuelva Pronto...');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(isset($request->btnLogin))
        {
            if($request->nombreUsuario=='pancho1')
            {
                if($request->claveUsuario=='esunareyna')
                {
                    $this->cargar_parametros_sistema(1);
                    return view('bienvenida');
                }else{
                    return view('login')->withErrors('No es la clave...');
                }

            }

            if($request->nombreUsuario=='pancho2')
            {
                if($request->claveUsuario=='confianza123')
                {
                    $this->cargar_parametros_sistema(2);
                    return view('bienvenida');
                }else{
                    return view('login')->withErrors('No es la clave...');
                }
            }
            return view('login')->withErrors('No existe usuario...');
        }

        if(isset($request->btnImprimir))
        {
            $nombre_impresora = "HP609B40 (HP DeskJet 4530 series) WIFI";  // HP609B40 (HP DeskJet 4530 series) WIFI
            $nombre_impresora = "smb://LINUX-7-99/wifi";
            $connector = new WindowsPrintConnector($nombre_impresora);
            //Impresora debe estar compartida
            //(documento local de bajo nivel) SALE EN LA COLA DE IMPRESIÓN PERO NO IMPRIME
            //$connector = new NetworkPrintConnector("192.168.0.15",9100);
            $printer = new Printer($connector);
            $printer->text("Hola mundo\nImpresión desde Mike42");

            $printer->feed();
            $printer->cut();
            $printer->close();

            return "IMPRESION";
        }

    }

    public function login()
    {
        return view('errors.loginvs',compact('mitok'));
    }

    public function loginvs(Request $r)
    {
        $resp="<html><body>Hola como estas ".$r->nombre."</body></html>";
        return $resp;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getAvatar($filename){
        
        $file = Storage::disk('users')->get($filename);

        return new Response($file,200);
    }
}
