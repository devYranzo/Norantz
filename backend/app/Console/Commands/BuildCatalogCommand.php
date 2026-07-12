<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Route as TransitRoute;
use App\Models\Stop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BuildCatalogCommand extends Command
{
    protected $signature = 'catalog:build';

    protected $description = 'Genera el bundle estático (stops + routes + agencies) que la app descarga de una sola vez';

    public function handle(): int
    {
        $this->info('Generando catálogo estático...');

        $agencies = Agency::all()->map(fn (Agency $agency) => [
            'id' => $agency->agency_id,
            'name' => $agency->name,
        ]);

        $stops = Stop::all()->map(fn (Stop $stop) => [
            'id' => $stop->stop_id,
            'name' => $stop->name,
            'latitude' => (float) $stop->latitude,
            'longitude' => (float) $stop->longitude,
            'modes' => $stop->modes ?? [],
            'regionId' => $stop->region_id,
            'agencyId' => $stop->agency_id,
            // Vacío aquí a propósito: se resuelve on-demand en GET /api/stops/{id},
            // igual que ya hacíamos en StopResource.
            'routes' => [],
        ]);

        $routes = TransitRoute::all()->map(fn (TransitRoute $route) => [
            'id' => $route->route_id,
            'shortName' => $route->short_name,
            'longName' => $route->long_name,
            'color' => $route->color,
            'mode' => $route->mode,
            'regionId' => $route->region_id,
            'agencyId' => $route->agency_id,
        ]);

        $version = (string) now()->timestamp;
        $updatedAt = now()->toIso8601String();

        $catalog = [
            'version' => $version,
            'updatedAt' => $updatedAt,
            'agencies' => $agencies,
            'stops' => $stops,
            'routes' => $routes,
        ];

        $meta = [
            'version' => $version,
            'updatedAt' => $updatedAt,
            'stopCount' => $stops->count(),
            'routeCount' => $routes->count(),
        ];

        // Disco local (no público): se sirve a través de CatalogController,
        // no hace falta storage:link ni exponer la carpeta directamente.
        Storage::disk('local')->put('catalog.json', json_encode($catalog));
        Storage::disk('local')->put('catalog_meta.json', json_encode($meta));

        $this->info("Catálogo generado: {$meta['stopCount']} paradas, {$meta['routeCount']} líneas (versión {$version}).");

        return self::SUCCESS;
    }
}
