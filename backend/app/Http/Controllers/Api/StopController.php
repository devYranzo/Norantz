<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StopResource;
use App\Models\Stop;

class StopController extends Controller {
    /**
     * El listado general de paradas ya lo cubre GET /api/catalog (bundle
     * completo, cacheado en el dispositivo). Este endpoint es solo para
     * el detalle de una parada concreta, donde sí calculamos sus rutas
     * reales (aquí el coste de la query extra es asumible, es una sola
     * parada, no un listado entero).
     */
    public function show(Stop $stop) {
        $stop->setAttribute('routesList', $stop->routes());

        return new StopResource($stop);
    }
}
