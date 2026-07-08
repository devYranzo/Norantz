<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopTime extends Model
{
    protected $fillable = [
        'trip_id',
        'stop_id',
        'stop_sequence',
        'arrival_time',
        'departure_time',
    ];

    protected $casts = [
        'stop_sequence' => 'integer',
        'arrival_time' => 'integer',
        'departure_time' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'trip_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'stop_id', 'stop_id');
    }
}
