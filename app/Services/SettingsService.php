<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Database-backed website settings with a config-defined schema and defaults.
 * All values are cached as a single array and flushed on every write.
 */
class SettingsService
{
    private const CACHE_KEY = 'cms.settings';

    private ?array $values = null;

    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            // Database unavailable (e.g. during install or a 500 page): fall back to defaults.
            $stored = [];
        }

        return $this->values = array_merge($this->defaults(), $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key, false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Public URL for an image setting (values are storage paths on the public disk).
     */
    public function url(string $key, ?string $fallbackKey = null): ?string
    {
        $path = $this->get($key) ?? ($fallbackKey ? $this->get($fallbackKey) : null);

        if (! $path) {
            return null;
        }

        return preg_match('#^https?://#', $path) ? $path : Storage::disk('public')->url($path);
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (int) $value : $value]);
        }

        $this->flush();
    }

    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
    }

    public function schema(): array
    {
        return config('settings', []);
    }

    public function group(string $group): array
    {
        return Arr::get($this->schema(), $group, []);
    }

    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->schema() as $group => $definition) {
            foreach ($definition['fields'] ?? [] as $key => $field) {
                $defaults["{$group}.{$key}"] = $field['default'] ?? null;
            }
        }

        return $defaults;
    }

    /**
     * Theme colours as CSS custom properties.
     */
    public function cssVariables(): string
    {
        $map = [
            '--brand-primary' => 'theme.primary_color',
            '--brand-secondary' => 'theme.secondary_color',
            '--brand-accent' => 'theme.accent_color',
            '--brand-button' => 'theme.button_color',
            '--brand-text' => 'theme.text_color',
            '--brand-background' => 'theme.background_color',
        ];

        return collect($map)
            ->map(function ($key, $var) {
                $value = (string) $this->get($key);

                return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? "{$var}:{$value}" : null;
            })
            ->filter()
            ->implode(';');
    }

    public function copyright(): string
    {
        return strtr((string) $this->get('general.copyright_text', ''), [
            '{year}' => now()->year,
            '{site}' => $this->get('branding.site_name'),
        ]);
    }
}
