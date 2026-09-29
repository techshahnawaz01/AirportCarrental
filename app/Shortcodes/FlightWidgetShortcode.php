<?php

namespace App\Shortcodes;

use App\Models\Page;
use App\Services\FlightDataService;

class FlightWidgetShortcode implements Shortcode
{
    public function __construct(private readonly FlightDataService $flights) {}

    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        return view('frontend.shortcodes.flight-widget', [
            'enabled' => $this->flights->airport() !== null,
            'arrivalsUrl' => Page::find(settings('integrations.arrivals_page_id'))?->url(),
            'departuresUrl' => Page::find(settings('integrations.departures_page_id'))?->url(),
            'title' => $attributes['title'] ?? 'Flight info',
            'overlap' => ($attributes['overlap'] ?? 'false') === 'true',
        ])->render();
    }
}
