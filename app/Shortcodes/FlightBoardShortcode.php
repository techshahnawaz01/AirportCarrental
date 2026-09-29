<?php

namespace App\Shortcodes;

use App\Models\Page;

class FlightBoardShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $direction = ($attributes['type'] ?? 'arrivals') === 'departures' ? 'departures' : 'arrivals';

        return view('frontend.shortcodes.flight-board', [
            'direction' => $direction,
            'airport' => settings('integrations.airport_name') ?: settings('integrations.airport_iata'),
        ])->render();
    }
}
