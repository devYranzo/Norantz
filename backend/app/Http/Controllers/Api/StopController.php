<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StopResource;
use App\Models\Stop;
use Illuminate\Http\Request;

class StopController extends Controller
{
    /**
     * Paradas dentro de un bounding box (lo que ya calculas en
     * useVisibleStops en el frontend, pero filtrado aquí en servidor
     * para no tener que cargar todas las paradas de la región de golpe).
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'min_lat' => ['required', 'numeric'],
            'max_lat' => ['required', 'numeric'],
            'min_lng' => ['required', 'numeric'],
            'max_lng' => ['required', 'numeric'],
            'modes' => ['sometimes', 'array'],
            'modes.*' => ['string', 'in:bus,tram,night_bus'],
        ]);

        $stops = Stop::query()
            ->whereBetween('latitude', [$validated['min_lat'], $validated['max_lat']])
            ->whereBetween('longitude', [$validated['min_lng'], $validated['max_lng']])
            ->when(! empty($validated['modes']), function ($query) use ($validated) {
                $query->where(function ($query) use ($validated) {
                    foreach ($validated['modes'] as $mode) {
                        $query->orWhereJsonContains('modes', $mode);
                    }
                });
            })
            ->get();

        return StopResource::collection($stops);
    }

    public function show(Stop $stop)
    {
        // Aquí sí calculamos las rutas reales: es una sola parada, así que
        // el coste de la query extra es asumible (a diferencia del listado).
        $stop->setAttribute('routesList', $stop->routes());

        return new StopResource($stop);
    }
}
