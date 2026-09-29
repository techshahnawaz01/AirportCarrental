<?php

namespace App\Services;

use App\Models\FlightSnapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Aviationstack integration: scheduled flight-board snapshots and
 * delay/cancellation summaries, rotating across the configured API keys.
 */
class FlightDataService
{
    private const BASE_URL = 'https://api.aviationstack.com/v1/flights';

    public function __construct(private readonly SettingsService $settings) {}

    public function enabled(): bool
    {
        return $this->airport() !== null && ! empty($this->keys());
    }

    public function airport(): ?string
    {
        $iata = strtoupper((string) $this->settings->get('integrations.airport_iata'));

        return strlen($iata) === 3 ? $iata : null;
    }

    /**
     * Latest stored board for arrivals|departures, normalised for display.
     */
    public function board(string $direction): array
    {
        $airport = $this->airport();
        if (! $airport) {
            return ['flights' => [], 'fetched_at' => null];
        }

        $snapshot = FlightSnapshot::where('airport_iata', $airport)
            ->where('direction', $direction)
            ->latest('fetched_at')
            ->first();

        return [
            'flights' => collect($snapshot?->data ?? [])->map(fn ($f) => $this->normalise($f, $direction))->values()->all(),
            'fetched_at' => $snapshot?->fetched_at,
        ];
    }

    /**
     * Fetch fresh arrivals and departures and store them as snapshots.
     *
     * @return array<string,int> number of flights stored per direction
     */
    public function sync(): array
    {
        $airport = $this->airport();
        $result = [];

        foreach (['arrivals' => 'arr_iata', 'departures' => 'dep_iata'] as $direction => $param) {
            $data = $this->request([$param => $airport, 'limit' => 100]);
            if ($data === null) {
                continue;
            }

            FlightSnapshot::create([
                'airport_iata' => $airport,
                'direction' => $direction,
                'data' => $data['data'] ?? [],
                'fetched_at' => now(),
            ]);
            $result[$direction] = count($data['data'] ?? []);
        }

        // Keep the table small: only the latest few snapshots per direction are useful.
        FlightSnapshot::where('fetched_at', '<', now()->subDays(2))->delete();

        return $result;
    }

    /**
     * Delayed and cancelled flights today, cached to protect the API quota.
     */
    public function disruptions(): array
    {
        $airport = $this->airport();
        if (! $airport) {
            return [];
        }

        return Cache::remember("flights.disruptions.{$airport}", config('cms.integrations.aviationstack.disruptions_ttl'), function () use ($airport) {
            $threshold = (int) config('cms.integrations.aviationstack.delay_threshold', 10);
            $calls = [
                ['cancelled', 'arrivals', ['flight_status' => 'cancelled', 'arr_iata' => $airport]],
                ['cancelled', 'departures', ['flight_status' => 'cancelled', 'dep_iata' => $airport]],
                ['delayed', 'arrivals', ['arr_iata' => $airport, 'min_delay_arr' => $threshold]],
                ['delayed', 'departures', ['dep_iata' => $airport, 'min_delay_dep' => $threshold]],
            ];

            $summary = ['generated_at' => now()->toIso8601String()];
            foreach ($calls as [$status, $direction, $params]) {
                $flights = collect($this->request($params + ['limit' => 100])['data'] ?? [])
                    ->filter(fn ($f) => ($f['flight_date'] ?? null) === now('UTC')->toDateString())
                    ->map(fn ($f) => $this->normalise($f, $direction))
                    ->values();

                $summary[$status][$direction] = ['count' => $flights->count(), 'flights' => $flights->all()];
            }

            return $summary;
        });
    }

    private function normalise(array $flight, string $direction): array
    {
        $side = $direction === 'departures' ? 'departure' : 'arrival';
        $other = $direction === 'departures' ? 'arrival' : 'departure';

        return [
            'date' => $flight['flight_date'] ?? null,
            'status' => $flight['flight_status'] ?? 'unknown',
            'airline' => Arr::get($flight, 'airline.name') ?: 'Unknown',
            'airline_iata' => Arr::get($flight, 'airline.iata'),
            'flight' => Arr::get($flight, 'flight.iata') ?: Arr::get($flight, 'flight.number'),
            'city' => Arr::get($flight, "{$other}.airport"),
            'city_iata' => Arr::get($flight, "{$other}.iata"),
            'scheduled' => Arr::get($flight, "{$side}.scheduled"),
            'estimated' => Arr::get($flight, "{$side}.estimated"),
            'delay' => Arr::get($flight, "{$side}.delay"),
            'terminal' => Arr::get($flight, "{$side}.terminal"),
            'gate' => Arr::get($flight, "{$side}.gate"),
            'baggage' => Arr::get($flight, 'arrival.baggage'),
        ];
    }

    private function keys(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->settings->secret('integrations.aviationstack_keys')))));
    }

    /**
     * Try each key until one succeeds; exhausted keys are skipped until next month.
     */
    private function request(array $params): ?array
    {
        $limit = (int) config('cms.integrations.aviationstack.monthly_limit_per_key', 100);
        $usageKey = 'flights.api_usage.'.now()->format('Y-m');
        $usage = Cache::get($usageKey, []);

        foreach ($this->keys() as $key) {
            $id = substr(sha1($key), 0, 12);
            if (($usage[$id] ?? 0) >= $limit) {
                continue;
            }

            try {
                $response = Http::timeout(15)->connectTimeout(5)->get(self::BASE_URL, ['access_key' => $key] + $params);
                $json = $response->json();
            } catch (Throwable $e) {
                Log::warning('Aviationstack request failed', ['message' => $e->getMessage()]);

                return null;
            }

            if (! empty($json['error'])) {
                if (in_array($json['error']['code'] ?? '', ['usage_limit_reached', 'invalid_access_key', 'inactive_user'], true)) {
                    $usage[$id] = $limit;
                    Cache::put($usageKey, $usage, now()->endOfMonth());

                    continue;
                }

                Log::warning('Aviationstack error', ['error' => $json['error']]);

                return null;
            }

            $usage[$id] = ($usage[$id] ?? 0) + 1;
            Cache::put($usageKey, $usage, now()->endOfMonth());

            return is_array($json) ? $json : null;
        }

        return null;
    }
}
