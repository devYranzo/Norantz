<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->stop_id,
            'name' => $this->name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'modes' => $this->modes ?? [],
            'regionId' => $this->region_id,
            'agencyId' => $this->agency_id,
            // Vacío en el listado del mapa (evita N+1), poblado en show().
            'routes' => TransitStopRouteResource::collection(collect($this->routesList ?? [])),
        ];
    }
}
