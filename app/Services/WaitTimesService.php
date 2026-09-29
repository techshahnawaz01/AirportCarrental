<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * TSA security wait times (tsawaittimes.com). Returns null when unavailable.
 */
class WaitTimesService
{
    public function forAirport(?string $iata): ?array
    {
        $key = config('cms.integrations.tsa_wait_times.key');
        $iata = strtoupper((string) $iata);

        if (! $key || strlen($iata) !== 3) {
            return null;
        }

        return Cache::remember("tsa.wait_times.{$iata}", config('cms.integrations.tsa_wait_times.ttl', 900), function () use ($key, $iata) {
            try {
                $data = Http::timeout(10)->get("https://www.tsawaittimes.com/api/airport/{$key}/{$iata}/json")->json();
            } catch (Throwable $e) {
                Log::warning('TSA wait times request failed', ['message' => $e->getMessage()]);

                return null;
            }

            if (! is_array($data) || ! isset($data['rightnow'])) {
                return null;
            }

            return [
                'name' => $data['name'] ?? $iata,
                'right_now' => $data['rightnow'],
                'description' => $data['rightnow_description'] ?? null,
                'precheck' => $data['precheck'] ?? null,
                'hourly' => collect($data['estimated_hourly_times'] ?? [])->map(fn ($slot) => [
                    'slot' => $slot['timeslot'] ?? '',
                    'minutes' => $slot['waittime'] ?? null,
                ])->all(),
                'checkpoints' => collect($data['precheck_checkpoints'] ?? [])->flatMap(fn ($checkpoints, $terminal) => collect($checkpoints)
                    ->map(fn ($status, $name) => ['terminal' => $terminal, 'checkpoint' => $name, 'status' => $status]))
                    ->values()->all(),
            ];
        });
    }
}
