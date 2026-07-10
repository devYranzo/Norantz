<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Trip $resource
 */
class RoutePatternResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trip = $this->resource;
        $route = $trip->route;

        return [
            'routeId' => $route->route_id,
            'directionId' => (string) ($trip->direction_id ?? '0'),
            'shapeId' => $trip->shape_id,
            'headsign' => $trip->headsign,
            'shortName' => $route->short_name,
            'longName' => $route->long_name,
            'color' => $route->color,
            'coordinates' => $trip->shape->coordinates ?? [],
            'regionId' => $route->region_id,
            'agencyId' => $route->agency_id,
            'mode' => $route->mode,
        ];
    }
}
