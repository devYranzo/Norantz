<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feeds GTFS estáticos
    |--------------------------------------------------------------------------
    |
    | Cada operador de Euskadi (Bilbobus, Bizkaibus, Euskotren, Metro Bilbao...)
    | publica su propio .zip en el catálogo de opendata.euskadi.eus. Rellena
    | aquí la URL exacta de descarga de cada uno (se ve en la ficha de cada
    | dataset) y ponla también en tu .env para no versionarla si cambia.
    |
    | La clave (ej. "bilbobus") es la que usarás al llamar al comando:
    |   php artisan gtfs:import bilbobus
    |
    */
    'feeds' => [
        'tuvisa' => env('GTFS_TUVISA_URL'),
        // añade aquí el resto de operadores que vayas a soportar
    ],

];
