<?php

namespace App\Shortcodes;

use App\Models\Media;
use App\Models\Page;

class CardShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $phone = settings('contact.phone');

        return view('frontend.shortcodes.card', [
            'title' => $attributes['title'] ?? '',
            'image' => isset($attributes['image']) ? Media::where('path', $attributes['image'])->first() : null,
            'content' => (string) $content,
            'button' => $attributes['button'] ?? ($phone ? 'Call to book' : null),
            'url' => $attributes['url'] ?? ($phone ? tel_href($phone) : null),
        ])->render();
    }
}
