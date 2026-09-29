<?php

namespace App\Shortcodes;

use App\Models\Media;
use App\Models\Page;

class CtaShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        return view('frontend.shortcodes.cta', [
            'title' => $attributes['title'] ?? '',
            'text' => $attributes['text'] ?? strip_tags((string) $content),
            'button' => $attributes['button'] ?? null,
            'url' => $attributes['url'] ?? null,
            'image' => isset($attributes['image']) ? Media::where('path', $attributes['image'])->first()?->url : null,
        ])->render();
    }
}
