<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoPublicoController extends Controller
{
    /**
     * Entrega archivos del disco public sin depender de un enlace simbólico
     * entre public_html/storage y storage/app/public.
     */
    public function mostrar(Request $request)
    {
        $ruta = str_replace('\\', '/', (string) $request->query('path', ''));
        $ruta = ltrim($ruta, '/');

        abort_if(strpos($ruta, '..') !== false, 404);

        if ($ruta === '' || !Storage::disk('public')->exists($ruta)) {
            return $this->imagenPorDefecto();
        }

        return Storage::disk('public')->response($ruta, null, [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function imagenPorDefecto()
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480" role="img" aria-labelledby="title description">
  <title id="title">Sin imagen disponible</title>
  <desc id="description">Imagen predeterminada para un repuesto sin fotografía</desc>
  <rect width="640" height="480" rx="24" fill="#f4f7fa"/>
  <rect x="18" y="18" width="604" height="444" rx="18" fill="none" stroke="#d6dee7" stroke-width="4"/>
  <g transform="translate(220 104)" fill="none" stroke="#8796a8" stroke-width="14" stroke-linecap="round" stroke-linejoin="round">
    <rect x="0" y="0" width="200" height="160" rx="18"/>
    <circle cx="150" cy="45" r="18" fill="#8796a8" stroke="none"/>
    <path d="M20 135l52-52 38 38 24-24 46 38"/>
  </g>
  <text x="320" y="326" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="700" fill="#526174">Sin imagen disponible</text>
  <text x="320" y="368" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" fill="#8796a8">Fotografía del repuesto</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
