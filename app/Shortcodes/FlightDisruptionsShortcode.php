<?php

namespace App\Shortcodes;

use App\Models\Page;

class FlightDisruptionsShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        return view('frontend.shortcodes.flight-disruptions', [
            'phone' => site_phone(),
        ])->render();
    }
}
