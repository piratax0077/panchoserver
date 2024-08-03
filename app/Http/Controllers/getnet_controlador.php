<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Dnetix\Redirection\Message\Notification;

use Dnetix\Redirection\PlacetoPay;

class getnet_controlador extends Controller
{

    public function index(Request $req){

try {
    //Creamos un objeto PlacetoPay con las credenciales entregadas por el equipo de desarrollo de getnet.
    $placetopay = new PlacetoPay([
        'login' => env('GETNET_CLIENT_ID'),
        'tranKey' => env('GETNET_CLIENT_SECRET'),
        'baseUrl' => env('GETNET_URL')
    ]);

} catch (\Exception $e) {
    return $e->getMessage();
}
        

        //Datos para pruebas con getnet

        /*$placetopay = new PlacetoPay([
            'login' => '7ffbb7bf1f7361b1200b2e8d74e1d76f',
            'tranKey' => 'SnZP3D63n3I9dH9O',
            'baseUrl' => 'https://checkout.test.getnet.cl'
        ]);*/

        $monto_total = $req->total;

        // Creating a random reference for the test
        $reference = 'SALE_' . time();

        // Request Information
        $request = [
            
            'payment' => [
                'reference' => $reference,
                'description' => 'Compra PanchoRepuestos',
                'amount' => [
                    'currency' => 'CLP',
                    'total' => $monto_total
                ],
            ],
            'expiration' => date('c', strtotime('+2 days')),
            'returnUrl' => 'https://www.panchorepuestos.cl/confirmacion_pago?reference=' . $req->numero_carrito,
            'cancelUrl' => 'https://www.panchorepuestos.cl/confirmacion_rechazo?reference=' . $req->numero_carrito,
            'noBuyerFill' => true,
            'ipAddress' => '127.0.0.1',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
        ];
        try {
      
            //$placetopay = placetopay();
            $response = $placetopay->request($request);
       
            if ($response->isSuccessful()) {
            
                // Redirect the client to the processUrl or display it on the JS extension
                return $response->processUrl();
            } else {
             
                // There was some error so check the message
                return $response->status()->message();
            }
            var_dump($response);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function notificacion(Request $req){
    // It if the data comes from the POST variable just leave it like this
            return $req;
    }

    public function confirmacion_pago(Request $req){
        $compra_id = $req->get('reference');
        return view('confirma-pago',['compra_id' => $compra_id]);
    }

    public function confirmacion_rechazo(Request $req){
        $compra_id = $req->get('reference');
        return view('confirma-rechazo',['compra_id' => $compra_id]);
    }
}
