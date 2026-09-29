<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Services\FlightDataService;
use App\Services\MediaService;
use App\Services\SettingsService;
use App\Services\WaitTimesService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SettingController extends AdminController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly MediaService $media,
    ) {}

    public function edit(?string $group = null)
    {
        $groups = collect($this->settings->schema())->filter(fn ($g) => ($g['screen'] ?? 'settings') === 'settings');
        $group ??= $groups->keys()->first();
        abort_unless($groups->has($group), 404);

        return view('admin.settings.edit', [
            'groups' => $groups,
            'group' => $group,
            'definition' => $groups[$group],
            'pages' => $this->pageOptions(),
        ]);
    }

    public function update(Request $request, string $group)
    {
        $definition = $this->settings->group($group);
        abort_if(empty($definition), 404);

        $fields = $definition['fields'];
        [$rules, $attributes] = $this->rules($fields);
        $validated = $request->validate($rules, [], $attributes);

        $values = [];
        foreach ($fields as $key => $field) {
            $fullKey = "{$group}.{$key}";

            if ($field['type'] === 'image') {
                if ($file = $request->file("uploads.{$key}")) {
                    $values[$fullKey] = $this->media->upload($file, $group === 'branding' ? 'branding' : null, $request->user()->id)->path;
                } elseif ($request->has("values.{$key}")) {
                    $values[$fullKey] = Arr::get($validated, "values.{$key}");
                }

                continue;
            }

            if ($field['type'] === 'secret') {
                // Blank means "keep the current key"; the clear checkbox removes it.
                if ($request->boolean("clear.{$key}")) {
                    $values[$fullKey] = null;
                } elseif (filled($secret = trim((string) Arr::get($validated, "values.{$key}")))) {
                    $values[$fullKey] = Crypt::encryptString($secret);
                }

                continue;
            }

            $value = Arr::get($validated, "values.{$key}");
            $values[$fullKey] = $field['type'] === 'toggle' ? (int) (bool) $value : (is_string($value) ? trim($value) : $value);
        }

        $this->settings->set($values);
        $this->log('updated', 'Updated '.Str::lower($definition['label']).' settings');

        if ($group === 'integrations') {
            $this->forgetIntegrationCaches();
        }

        return $this->success($definition['label'].' settings saved.', [
            'reload' => $group === 'integrations',
            'images' => collect($fields)->filter(fn ($f) => $f['type'] === 'image')
                ->mapWithKeys(fn ($f, $key) => [$key => $this->settings->url("{$group}.{$key}")]),
        ]);
    }

    /**
     * Fetch fresh wait times and flight data so admins can verify their keys.
     */
    public function testIntegrations(WaitTimesService $waitTimes, FlightDataService $flights)
    {
        $this->forgetIntegrationCaches();
        $iata = settings('integrations.airport_iata');
        $results = [];

        if (! $this->settings->secret('integrations.tsa_api_key')) {
            $results[] = 'TSA wait times: no API key set.';
        } elseif (! $iata) {
            $results[] = 'TSA wait times: set the airport IATA code first.';
        } else {
            $data = $waitTimes->forAirport($iata);
            $results[] = $data
                ? "TSA wait times: working — {$data['right_now']} min right now at {$iata}."
                : 'TSA wait times: the API did not return data. Check the key and airport code.';
        }

        if (! $flights->enabled()) {
            $results[] = 'Flights: add Aviationstack keys and the airport IATA code to enable.';
        } else {
            $synced = $flights->sync();
            $results[] = $synced
                ? 'Flights: updated — '.collect($synced)->map(fn ($n, $d) => "{$n} {$d}")->implode(', ').'.'
                : 'Flights: no data returned. The keys may be invalid or have reached their monthly limit.';
        }

        $ok = ! collect($results)->contains(fn ($r) => str_contains($r, 'did not') || str_contains($r, 'no data'));
        $this->log('updated', 'Refreshed integration data');

        return $ok
            ? $this->success(implode(' ', $results), ['results' => $results])
            : $this->failure(implode(' ', $results), 422);
    }

    private function forgetIntegrationCaches(): void
    {
        foreach (array_filter([settings('integrations.airport_iata')]) as $iata) {
            Cache::forget('tsa.wait_times.'.strtoupper($iata));
            Cache::forget('flights.disruptions.'.strtoupper($iata));
        }
    }

    public function destroyImage(string $key)
    {
        [$group, $field] = explode('.', $key, 2);
        abort_unless(($this->settings->group($group)['fields'][$field]['type'] ?? null) === 'image', 404);

        $this->settings->set([$key => null]);
        $this->log('updated', 'Removed '.$key.' image');

        return $this->success('Image removed.');
    }

    private function rules(array $fields): array
    {
        $rules = [];
        $attributes = [];

        foreach ($fields as $key => $field) {
            $attributes["values.{$key}"] = $attributes["uploads.{$key}"] = Str::lower($field['label']);
            $fieldRules = is_array($field['rules'] ?? null) ? $field['rules'] : explode('|', (string) ($field['rules'] ?? 'nullable'));

            if ($field['type'] === 'image') {
                $rules["uploads.{$key}"] = array_merge(['nullable'], $fieldRules);
                $rules["values.{$key}"] = ['nullable', 'string', 'max:500', 'exists:media,path'];
            } elseif ($field['type'] === 'secret') {
                $rules["values.{$key}"] = $fieldRules;
                $rules["clear.{$key}"] = ['nullable', 'boolean'];
            } elseif ($field['type'] === 'toggle') {
                $rules["values.{$key}"] = ['nullable', 'boolean'];
            } else {
                $rules["values.{$key}"] = $fieldRules;
            }
        }

        return [$rules, $attributes];
    }

    private function pageOptions()
    {
        return Page::orderBy('path')->get(['id', 'title', 'path']);
    }
}
