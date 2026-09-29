<?php

use App\Services\SettingsService;
use Illuminate\Support\Carbon;

if (! function_exists('settings')) {
    /**
     * settings() → service, settings('group.key', $default) → value.
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        return $key === null ? $service : $service->get($key, $default);
    }
}

if (! function_exists('local_date')) {
    /**
     * Format a date in the site timezone configured in settings.
     */
    function local_date(mixed $date, string $format = 'M j, Y'): string
    {
        if (! $date) {
            return '';
        }

        return Carbon::parse($date)->timezone(settings('general.timezone', config('app.timezone')))->format($format);
    }
}

if (! function_exists('tel_href')) {
    function tel_href(?string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', (string) $phone);
    }
}
