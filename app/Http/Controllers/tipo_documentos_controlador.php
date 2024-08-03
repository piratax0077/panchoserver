<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\tipo_documento;

class tipo_documentos_controlador extends Controller
{
    public function dame_tipos(){
        $tipos=tipo_documento::All();
        return $tipos;
    }
}
