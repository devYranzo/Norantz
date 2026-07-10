<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransitStopRouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->route_id,
            'name' => $this->short_name ?? $this->long_name,
        ];
    }
}
