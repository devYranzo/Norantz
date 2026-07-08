<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    protected $primaryKey = 'agency_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'agency_id',
        'name',
        'url',
        'timezone',
        'lang',
        'phone',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class, 'agency_id', 'agency_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'agency_id', 'agency_id');
    }
}
