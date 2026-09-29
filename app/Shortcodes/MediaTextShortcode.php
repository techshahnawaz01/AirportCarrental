<?php

namespace App\Shortcodes;

use App\Models\Media;
use App\Models\Page;
use App\Services\ShortcodeRenderer;

class MediaTextShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        return view('frontend.shortcodes.media-text', [
            'image' => isset($attributes['image']) ? Media::where('path', $attributes['image'])->first() : null,
            'title' => $attributes['title'] ?? null,
            'heading' => in_array($attributes['heading'] ?? 'h2', ['h1', 'h2', 'h3'], true) ? ($attributes['heading'] ?? 'h2') : 'h2',
            'reverse' => ($attributes['position'] ?? 'left') === 'right',
            'content' => app(ShortcodeRenderer::class)->render($content, $page),
        ])->render();
    }
}
