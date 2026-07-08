<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarDate extends Model
{
    protected $fillable = [
        'service_id',
        'date',
        'exception_type',
    ];

    protected $casts = [
        'date' => 'date',
        'exception_type' => 'integer',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class, 'service_id', 'service_id');
    }
}
