<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpdf\Mpdf;
use App\carrito_compra;
use Dompdf\Dompdf;
use Session;
use Illuminate\Support\Facades\Auth;

class pdf_controlador extends Controller
{
    private function validaSesion()
    {
        //Valida sesión: REVISAR: repuestos/Exceptions/Handler.php, método render()
        abort_if(Session::get('acceso')!='SI', 403);
    }

    private function dametotalcarrito()
    {
        $total=carrito_compra::where('usuarios_id',Auth::user()->id)->sum('total_item');
        return $total;
    }


    private function configurarPDF()
    {
            /* NOTA: Por ahora no uso
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            */

            $mpdf = new Mpdf([
                'mode'=>'utf-8',
                'format'=>[80,297],  //en mm ancho x alto
                'margin_header'=>0,
                'margin_top'=>0,
                'margin_footer'=>0,
                'margin_bottom'=>0,
                'margin_left'=>0,
                'margin_right'=>0,
                'orientation'=>'P',
                ]);

            /* OJO: NO FUNCIONA
            $mpdf->AddFontDirectory(resource_path('mis_letras'));

            $mpdf->fontdata['alfredito']=[
                'R'=>'alfredito.ttf'
            ];
            */

            $mpdf->SetAuthor("Ing. Jesús Tejerina Rivera");
            $mpdf->SetTitle("Pancho Repuestos"); //OJO: Aquí se puede poner el número de documento como boleta, factura, guia despacho, nota credito o debito
            $mpdf->SetSubject("Boleta N° ???");
            $mpdf->SetDisplayMode('fullwidth'); //OJO: https://mpdf.github.io/reference/mpdf-functions/setdisplaymode.html
            return $mpdf;

    }

    private function damehtml()
    {
        $carrito=carrito_compra::select('carrito_compras.id',
        'carrito_compras.cantidad',
        'carrito_compras.pu',
        'carrito_compras.subtotal_item',
        'carrito_compras.descuento_item',
        'carrito_compras.total_item',
        'repuestos.codigo_interno',
        'repuestos.descripcion')
        ->where('carrito_compras.usuarios_id',Auth::user()->id)
        ->join('repuestos','carrito_compras.id_repuestos','repuestos.id')
        ->get();
        $total=$this->dametotalcarrito();
        $html=view('inventario.boleta',compact('carrito','total'))->render();
        return $html;
    }

    public function verboleta() //OJO: Sólo temporal
    {
        return $this->damehtml();
    }

    public function pdf()
    {
        // https://desarrollowebtutorial.com/generar-pdf-en-laravel/
        //$this->validaSesion();
        try{
            //
            $boleta_donde="./storage/pdf/";
            $archivo="boleta_".time().".pdf";
            $mpdf=$this->configurarPDF();
            $html=$this->damehtml();
            $mpdf->WriteHTML($html);
            $mpdf->Output($donde.$archivo,"F"); /* OJO: OUTPUT  F: Guardar el PDF  D: Descargar el PDF    I:  InLine Browser    */
            return $donde.$archivo;
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

        /*
        switch ($r->docu)
        {
            case "cotizacion" :

            break;
            case "boleta" :

            break;
            case "factura" :

            break;
            default:
        }
        */



    }

    public function generatePdf()
    {
        
        // Creamos una instancia de Dompdf
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $html = '<!DOCTYPE html>
            <html>
            <head>
            <meta charset="utf-8">
            <title>Ejemplo de imagen en Dompdf</title>
            </head>
            <body>
            <h1>Imagen de ejemplo</h1>
            <img src="https://panchoserver.ddns.net/storage/imagenes/logo_pos.png" alt="." />
            </body>
            </html>';

        $dompdf->loadHtml($html);
        $dompdf->render();

        $dompdf->stream('ejemplo.pdf', array('Attachment' => 0));

    }
}
