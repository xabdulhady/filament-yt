<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightSegment extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'sequence',
        'flight_id',
        'airport_id',
        'time'
    ];

    public function flight(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function airport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

}
