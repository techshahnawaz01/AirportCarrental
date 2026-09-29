<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = ['airport_iata', 'direction', 'data', 'fetched_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'fetched_at' => 'datetime'];
    }
}
