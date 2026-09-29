<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FlightDataService;

class FlightController extends Controller
{
    public function __construct(private readonly FlightDataService $flights) {}

    public function board(string $direction)
    {
        $board = $this->flights->board($direction);

        return $this->success('Flights loaded.', [
            'flights' => $board['flights'],
            'fetched_at' => $board['fetched_at']?->toIso8601String(),
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function disruptions()
    {
        if (! $this->flights->enabled()) {
            return $this->failure('Live flight data is not available right now.', 503);
        }

        return $this->success('Disruptions loaded.', $this->flights->disruptions())
            ->header('Cache-Control', 'public, max-age=600');
    }
}
