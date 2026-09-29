<?php

namespace App\Shortcodes;

use App\Models\Page;

class ContactFormShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        return view('frontend.shortcodes.contact-form', [
            'title' => $attributes['title'] ?? null,
            'subject' => $attributes['subject'] ?? null,
        ])->render();
    }
}
