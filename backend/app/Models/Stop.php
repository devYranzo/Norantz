<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stop extends Model
{
    protected $primaryKey = 'stop_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stop_id',
        'agency_id',
        'region_id',
        'name',
        'latitude',
        'longitude',
        'modes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'modes' => 'array',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'agency_id');
    }

    public function stopTimes(): HasMany
    {
        return $this->hasMany(StopTime::class, 'stop_id', 'stop_id');
    }

    /**
     * Rutas que pasan por esta parada. No es una relación many-to-many
     * directa (va stops -> stop_times -> trips -> routes), así que la
     * resolvemos con una query en vez de una relación de Eloquent.
     */
    public function routes()
    {
        return Route::query()
            ->whereIn('route_id', function ($query) {
                $query->select('trips.route_id')
                    ->from('trips')
                    ->join('stop_times', 'stop_times.trip_id', '=', 'trips.trip_id')
                    ->where('stop_times.stop_id', $this->stop_id);
            })
            ->get();
    }
}
