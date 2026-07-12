<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Calendar;
use App\Models\CalendarDate;
use App\Models\Route as TransitRoute;
use App\Models\Shape;
use App\Models\Stop;
use App\Models\StopTime;
use App\Models\Trip;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class ImportGtfsData extends Command
{
    protected $signature = 'gtfs:import {agency : Clave del operador definida en config/gtfs.php}';

    protected $description = 'Descarga el GTFS estático de un operador y actualiza la base de datos';

    private const CHUNK_SIZE = 500;

    public function handle(): int
    {
        // Los imports GTFS grandes (Bilbobus, Bizkaibus...) pueden superar
        // fácilmente el memory_limit por defecto de PHP para peticiones web
        // (128M). Como esto es un comando CLI, no una petición HTTP, subirlo
        // aquí es seguro y no afecta a tu configuración de producción.
        ini_set('memory_limit', '1G');

        $agencyKey = $this->argument('agency');
        $url = config("gtfs.feeds.{$agencyKey}");

        if (! $url) {
            $this->error("No hay una URL configurada para '{$agencyKey}' en config/gtfs.php (o en tu .env).");

            return self::FAILURE;
        }

        $zipPath = $this->download($url, $agencyKey);
        $extractPath = $this->extract($zipPath, $agencyKey);

        DB::transaction(function () use ($extractPath, $agencyKey) {
            $this->info('Importando agencia...');
            $this->importAgency($extractPath);

            $this->info('Importando calendario...');
            $this->importCalendar($extractPath);
            $this->ensureCalendarStubs($extractPath);
            $this->importCalendarDates($extractPath);

            $this->info('Importando shapes...');
            $this->importShapes($extractPath);

            $this->info('Importando rutas...');
            $this->importRoutes($extractPath, $agencyKey);

            $this->info('Importando paradas...');
            $this->importStops($extractPath, $agencyKey);

            $this->info('Importando trips...');
            $this->importTrips($extractPath);

            $this->info('Importando stop_times (puede tardar)...');
            $this->importStopTimes($extractPath);

            $this->syncStopModes();
        });

        $this->info("GTFS de '{$agencyKey}' importado correctamente.");

        $this->call('catalog:build');

        return self::SUCCESS;
    }

    private function download(string $url, string $agencyKey): string
    {
        $this->info("Descargando GTFS de '{$agencyKey}'...");

        $directory = storage_path('app/gtfs');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $zipPath = "{$directory}/{$agencyKey}.zip";

        // sink() vuelca la respuesta directo a disco en streaming,
        // así no cargamos el zip entero en memoria.
        Http::withOptions(['sink' => $zipPath])->get($url)->throw();

        return $zipPath;
    }

    private function extract(string $zipPath, string $agencyKey): string
    {
        $this->info('Descomprimiendo...');

        $extractPath = storage_path("app/gtfs/{$agencyKey}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("No se ha podido abrir el zip de '{$agencyKey}'.");
        }

        $zip->extractTo($extractPath);
        $zip->close();

        return $extractPath;
    }

    /**
     * Lee un CSV con cabecera fila por fila sin cargarlo entero en memoria.
     */
    private function readCsv(string $filePath): Generator
    {
        if (! file_exists($filePath)) {
            return;
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        // Los GTFS de Euskadi a veces vienen con BOM al principio del fichero.
        if ($header !== false) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue; // fila corrupta o línea en blanco, la saltamos
            }

            yield array_combine($header, $row);
        }

        fclose($handle);
    }

    /**
     * Convierte "25:10:00" (GTFS permite pasar de medianoche) a segundos.
     */
    private function timeToSeconds(string $time): int
    {
        [$h, $m, $s] = array_map('intval', explode(':', $time));

        return $h * 3600 + $m * 60 + $s;
    }

    /**
     * GTFS define más tipos (metro, tren, funicular, ferry...) de los que
     * tu enum TransitMode contempla ahora mismo (bus/tram/night_bus).
     * De momento todo lo que no sea tranvía se importa como "bus" para
     * no romper el import; cuando amplíes el enum, actualiza este mapeo.
     */
    private function mapRouteType(string $routeType): string
    {
        return match ((int) $routeType) {
            0 => 'tram',
            default => 'bus',
        };
    }

    private function importAgency(string $path): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/agency.txt") as $row) {
            $rows[] = [
                'agency_id' => $row['agency_id'] ?? $row['agency_name'],
                'name' => $row['agency_name'],
                'url' => $row['agency_url'] ?? null,
                'timezone' => $row['agency_timezone'] ?? 'Europe/Madrid',
                'lang' => $row['agency_lang'] ?? null,
                'phone' => $row['agency_phone'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        if ($rows !== []) {
            Agency::upsert($rows, ['agency_id'], ['name', 'url', 'timezone', 'lang', 'phone', 'updated_at']);
        }
    }

    private function importCalendar(string $path): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/calendar.txt") as $row) {
            $rows[] = [
                'service_id' => $row['service_id'],
                'monday' => (bool) $row['monday'],
                'tuesday' => (bool) $row['tuesday'],
                'wednesday' => (bool) $row['wednesday'],
                'thursday' => (bool) $row['thursday'],
                'friday' => (bool) $row['friday'],
                'saturday' => (bool) $row['saturday'],
                'sunday' => (bool) $row['sunday'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            Calendar::upsert(
                $chunk,
                ['service_id'],
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'start_date', 'end_date', 'updated_at']
            );
        }
    }

    /**
     * GTFS permite que un service_id se defina solo en calendar_dates.txt
     * (o incluso que solo aparezca referenciado desde trips.txt), sin fila
     * propia en calendar.txt. Como calendar_dates.service_id y
     * trips.service_id tienen FK contra calendars, aquí creamos una fila
     * "stub" (ningún día de la semana activo) para cualquier service_id
     * que no exista todavía, usando el rango de fechas de sus excepciones
     * como start_date/end_date orientativos.
     */
    private function ensureCalendarStubs(string $path): void
    {
        $existing = Calendar::pluck('service_id')->flip();

        $referenced = [];
        foreach ($this->readCsv("{$path}/calendar_dates.txt") as $row) {
            $referenced[$row['service_id']][] = $row['date'];
        }
        foreach ($this->readCsv("{$path}/trips.txt") as $row) {
            $referenced[$row['service_id']] ??= [];
        }

        $stubs = [];
        foreach ($referenced as $serviceId => $dates) {
            if (isset($existing[$serviceId])) {
                continue;
            }

            $stubs[] = [
                'service_id' => $serviceId,
                'monday' => false,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
                'saturday' => false,
                'sunday' => false,
                'start_date' => $dates !== [] ? min($dates) : '1970-01-01',
                'end_date' => $dates !== [] ? max($dates) : '2099-12-31',
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($stubs, self::CHUNK_SIZE) as $chunk) {
            Calendar::upsert(
                $chunk,
                ['service_id'],
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'start_date', 'end_date', 'updated_at']
            );
        }
    }

    private function importCalendarDates(string $path): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/calendar_dates.txt") as $row) {
            $rows[] = [
                'service_id' => $row['service_id'],
                'date' => $row['date'],
                'exception_type' => (int) $row['exception_type'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            CalendarDate::upsert($chunk, ['service_id', 'date'], ['exception_type', 'updated_at']);
        }
    }

    private function importShapes(string $path): void
    {
        // shapes.txt viene como un punto por fila; los agrupamos por shape_id
        // y ordenamos por secuencia antes de guardarlos como coordinates JSON.
        $points = [];
        foreach ($this->readCsv("{$path}/shapes.txt") as $row) {
            $points[$row['shape_id']][(int) $row['shape_pt_sequence']] = [
                (float) $row['shape_pt_lat'],
                (float) $row['shape_pt_lon'],
            ];
        }

        $rows = [];
        foreach ($points as $shapeId => $sequencedPoints) {
            ksort($sequencedPoints);
            $rows[] = [
                'shape_id' => $shapeId,
                'coordinates' => json_encode(array_values($sequencedPoints)),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            Shape::upsert($chunk, ['shape_id'], ['coordinates', 'updated_at']);
        }
    }

    private function importRoutes(string $path, string $agencyKey): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/routes.txt") as $row) {
            $rows[] = [
                'route_id' => $row['route_id'],
                'agency_id' => $row['agency_id'] ?? null,
                'region_id' => $agencyKey,
                'short_name' => $row['route_short_name'] ?? null,
                'long_name' => $row['route_long_name'] ?? null,
                'color' => isset($row['route_color']) && $row['route_color'] !== ''
                    ? '#'.ltrim($row['route_color'], '#')
                    : null,
                'mode' => $this->mapRouteType($row['route_type'] ?? '3'),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            TransitRoute::upsert(
                $chunk,
                ['route_id'],
                ['agency_id', 'region_id', 'short_name', 'long_name', 'color', 'mode', 'updated_at']
            );
        }
    }

    private function importStops(string $path, string $agencyKey): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/stops.txt") as $row) {
            $rows[] = [
                'stop_id' => $row['stop_id'],
                'agency_id' => null, // stops.txt no trae agency_id en GTFS estándar
                'region_id' => $agencyKey,
                'name' => $row['stop_name'],
                'latitude' => (float) $row['stop_lat'],
                'longitude' => (float) $row['stop_lon'],
                'modes' => null, // se rellena después en syncStopModes()
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            Stop::upsert(
                $chunk,
                ['stop_id'],
                ['region_id', 'name', 'latitude', 'longitude', 'updated_at']
            );
        }
    }

    private function importTrips(string $path): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/trips.txt") as $row) {
            $rows[] = [
                'trip_id' => $row['trip_id'],
                'route_id' => $row['route_id'],
                'service_id' => $row['service_id'],
                'shape_id' => $row['shape_id'] ?? null,
                'headsign' => $row['trip_headsign'] ?? null,
                'direction_id' => isset($row['direction_id']) && $row['direction_id'] !== ''
                    ? (int) $row['direction_id']
                    : null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            Trip::upsert(
                $chunk,
                ['trip_id'],
                ['route_id', 'service_id', 'shape_id', 'headsign', 'direction_id', 'updated_at']
            );
        }
    }

    private function importStopTimes(string $path): void
    {
        $rows = [];
        foreach ($this->readCsv("{$path}/stop_times.txt") as $row) {
            $rows[] = [
                'trip_id' => $row['trip_id'],
                'stop_id' => $row['stop_id'],
                'stop_sequence' => (int) $row['stop_sequence'],
                'arrival_time' => $this->timeToSeconds($row['arrival_time']),
                'departure_time' => $this->timeToSeconds($row['departure_time']),
                'updated_at' => now(),
                'created_at' => now(),
            ];

            // stop_times.txt puede tener cientos de miles de filas: volcamos
            // cada CHUNK_SIZE en vez de acumular todo en memoria.
            if (count($rows) >= self::CHUNK_SIZE) {
                StopTime::upsert($rows, ['trip_id', 'stop_sequence'], ['stop_id', 'arrival_time', 'departure_time', 'updated_at']);
                $rows = [];
            }
        }

        if ($rows !== []) {
            StopTime::upsert($rows, ['trip_id', 'stop_sequence'], ['stop_id', 'arrival_time', 'departure_time', 'updated_at']);
        }
    }

    /**
     * GTFS no dice en stops.txt qué modos pasan por cada parada; lo deducimos
     * cruzando stop_times -> trips -> routes, una vez ya están todos importados.
     */
    private function syncStopModes(): void
    {
        $this->info('Calculando modos de transporte por parada...');

        $stopModes = DB::table('stop_times')
            ->join('trips', 'trips.trip_id', '=', 'stop_times.trip_id')
            ->join('routes', 'routes.route_id', '=', 'trips.route_id')
            ->select('stop_times.stop_id', 'routes.mode')
            ->distinct()
            ->get()
            ->groupBy('stop_id')
            ->map(fn ($rows) => $rows->pluck('mode')->unique()->values()->all());

        foreach ($stopModes as $stopId => $modes) {
            Stop::where('stop_id', $stopId)->update(['modes' => json_encode($modes)]);
        }
    }
}
