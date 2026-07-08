<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $primaryKey = 'route_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'route_id',
        'agency_id',
        'region_id',
        'short_name',
        'long_name',
        'color',
        'mode',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'agency_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'route_id', 'route_id');
    }
}
