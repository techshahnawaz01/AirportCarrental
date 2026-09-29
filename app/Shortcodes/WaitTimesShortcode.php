<?php

namespace App\Shortcodes;

use App\Models\Page;
use App\Services\WaitTimesService;

class WaitTimesShortcode implements Shortcode
{
    public function __construct(private readonly WaitTimesService $waitTimes) {}

    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $iata = $attributes['iata'] ?? settings('integrations.airport_iata');

        return view('frontend.shortcodes.wait-times', [
            'data' => $this->waitTimes->forAirport($iata),
            'title' => $attributes['title'] ?? 'Security wait times',
        ])->render();
    }
}
