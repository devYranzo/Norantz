<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shape extends Model
{
    protected $primaryKey = 'shape_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shape_id',
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'shape_id', 'shape_id');
    }
}
