<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $primaryKey = 'trip_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'trip_id',
        'route_id',
        'service_id',
        'shape_id',
        'headsign',
        'direction_id',
    ];

    protected $casts = [
        'direction_id' => 'integer',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_id', 'route_id');
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class, 'service_id', 'service_id');
    }

    public function shape(): BelongsTo
    {
        return $this->belongsTo(Shape::class, 'shape_id', 'shape_id');
    }

    public function stopTimes(): HasMany
    {
        return $this->hasMany(StopTime::class, 'trip_id', 'trip_id')->orderBy('stop_sequence');
    }
}
