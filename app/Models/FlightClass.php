<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightClass extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'flight_id',
        'class_type',
        'price',
        'total_seats'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function transaction(): HasOne|FlightClass
    {
        return $this->hasOne(Transaction::class, 'flight_class_id');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'flight_class_facility', 'flight_class_id', 'facility_id');
    }

}
