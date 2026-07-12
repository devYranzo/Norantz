<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    /**
     * Ligero a propósito: la app lo consulta primero para decidir si
     * necesita descargar el bundle completo o si el que tiene en disco
     * sigue siendo válido.
     */
    public function meta()
    {
        if (! Storage::disk('local')->exists('catalog_meta.json')) {
            return response()->json([
                'message' => 'El catálogo aún no se ha generado. Ejecuta php artisan catalog:build.',
            ], 404);
        }

        return response(Storage::disk('local')->get('catalog_meta.json'))
            ->header('Content-Type', 'application/json');
    }

    public function show()
    {
        if (! Storage::disk('local')->exists('catalog.json')) {
            return response()->json([
                'message' => 'El catálogo aún no se ha generado. Ejecuta php artisan catalog:build.',
            ], 404);
        }

        return response(Storage::disk('local')->get('catalog.json'))
            ->header('Content-Type', 'application/json');
    }
}
