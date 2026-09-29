<?php

namespace App\Shortcodes;

use App\Models\Page;

/**
 * Call-to-action that dials the phone number configured in Settings → Contact.
 */
class CallCtaShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $phone = $attributes['phone'] ?? settings('contact.phone') ?? settings('general.phone');

        if (! $phone) {
            return '';
        }

        return view('frontend.shortcodes.call-cta', [
            'title' => $attributes['title'] ?? 'Need help with your trip?',
            'text' => $attributes['text'] ?? null,
            'button' => $attributes['button'] ?? 'Call now',
            'phone' => $phone,
            'label' => settings('contact.phone_label'),
        ])->render();
    }
}
