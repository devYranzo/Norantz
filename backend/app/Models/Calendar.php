<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    protected $primaryKey = 'service_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'service_id', 'service_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(CalendarDate::class, 'service_id', 'service_id');
    }
}
