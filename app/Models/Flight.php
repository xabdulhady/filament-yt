<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'flight_number',
        'airline_id'
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function flightSegments(): Flight|HasMany
    {
        return $this->hasMany(FlightSegment::class, 'flight_id');
    }

    public function flightClasses(): Flight|HasMany
    {
        return $this->hasMany(FlightClass::class, 'flight_id');
    }

    public function flightSeats(): Flight|HasMany
    {
        return $this->hasMany(FlightSeat::class, 'flight_id');
    }

    public function transactions(): Flight|HasMany
    {
        return $this->hasMany(Transaction::class, 'flight_id');
    }

    public function generateSeats(): void
    {
        $classes = $this->flightClasses;

        foreach ($classes as $class) {
            $totalSeats = $class->total_seats;
            $seatsPerRow = $this->getSeatsPerRow($class->class_type);
            $rows = ceil($totalSeats / $seatsPerRow);

            $existingSeats = FlightSeat::where('flight_id', $this->id)
                ->where('class_type', $class->class_type)
                ->get();

            $existingRows = $existingSeats->pluck('row')->toArray();
            $seatCounter = 1;

            for ($row = 1; $row <= $rows; $row++) {
                if (!in_array($row, $existingRows)) {
                    for ($column = 1; $column <= $seatsPerRow; $column++) {
                        if ($seatCounter > $totalSeats) {
                            break;
                        }

                        $seatCode = $this->generateSeatCode($row, $column);

                        FlightSeat:: create([
                            'flight_id' => $this->id,
                            'name' => $seatCode,
                            'row' => $row,
                            'column' => $column,
                            'is available' => true,
                            'class type' => $class->class_type,
                        ]);

                        $seatCounter++;
                    }
                }
            }

            foreach ($existingSeats as $existingSeat) {
                if ($existingSeat->column > $seatsPerRow || $existingSeat->row > $rows) {
                    $existingSeat->is_available = false;
                    $existingSeat->save();
                }
            }

        }
    }

    protected function getSeatsPerRow($classType): int
    {
        return match ($classType) {
            'business' => 4,
            'economy' => 6,
            default => 0,
        };
    }

    private function generateSeatCode($row, $column): string
    {
        return chr(64 + $row) . $column;
    }

}
