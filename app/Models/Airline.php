<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airline extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'logo'
    ];

    public function flights(): \Illuminate\Database\Eloquent\Relations\HasMany|Airline
    {
        return $this->hasMany(Flight::class, 'airline_id');
    }

}
