<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoutePatternResource;
use App\Models\Route as TransitRoute;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    /**
     * Devuelve una entrada por cada combinación route_id + direction_id
     * (ida/vuelta), no una por cada route_id: una misma línea puede tener
     * trazados distintos según el sentido, y el frontend necesita
     * "coordinates" ya resuelto para poder dibujar cada uno.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'region_id' => ['sometimes', 'string'],
            'agency_id' => ['sometimes', 'string'],
            'mode' => ['sometimes', 'string', 'in:bus,tram,night_bus'],
        ]);

        $routeIds = TransitRoute::query()
            ->when($request->filled('region_id'), fn ($q) => $q->where('region_id', $validated['region_id']))
            ->when($request->filled('agency_id'), fn ($q) => $q->where('agency_id', $validated['agency_id']))
            ->when($request->filled('mode'), fn ($q) => $q->where('mode', $validated['mode']))
            ->pluck('route_id');

        $patterns = DB::table('trips')
            ->select('route_id', 'direction_id', DB::raw('MIN(trip_id) as sample_trip_id'))
            ->whereIn('route_id', $routeIds)
            ->groupBy('route_id', 'direction_id')
            ->get();

        $sampleTrips = Trip::with(['route', 'shape'])
            ->whereIn('trip_id', $patterns->pluck('sample_trip_id'))
            ->get()
            ->keyBy('trip_id');

        $result = $patterns
            ->map(fn ($pattern) => $sampleTrips->get($pattern->sample_trip_id))
            ->filter()
            ->values();

        return RoutePatternResource::collection($result);
    }

    public function show(TransitRoute $route)
    {
        $representativeTrips = Trip::with('shape')
            ->where('route_id', $route->route_id)
            ->get()
            ->groupBy('direction_id')
            ->map(function ($tripsInDirection) use ($route) {
                $trip = $tripsInDirection->first();
                $trip->setRelation('route', $route);

                return $trip;
            })
            ->values();

        return RoutePatternResource::collection($representativeTrips);
    }
}
