<?php

namespace App\Console\Commands;

use App\Services\FlightDataService;
use Illuminate\Console\Command;

class SyncFlights extends Command
{
    protected $signature = 'flights:sync';

    protected $description = 'Fetch the latest arrivals and departures for the configured airport';

    public function handle(FlightDataService $flights): int
    {
        if (! $flights->enabled()) {
            $this->components->info('Flight integration is not configured (airport IATA code or AVIATIONSTACK_KEYS missing). Nothing to do.');

            return self::SUCCESS;
        }

        $result = $flights->sync();

        if (empty($result)) {
            $this->components->warn('No flight data could be fetched. All API keys may have reached their monthly limit.');

            return self::FAILURE;
        }

        foreach ($result as $direction => $count) {
            $this->components->twoColumnDetail(ucfirst($direction), $count.' flights');
        }

        return self::SUCCESS;
    }
}
