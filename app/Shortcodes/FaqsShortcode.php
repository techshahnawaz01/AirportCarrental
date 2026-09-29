<?php

namespace App\Shortcodes;

use App\Models\Page;

class FaqsShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $source = isset($attributes['page']) ? Page::published()->where('path', trim($attributes['page'], '/'))->first() : $page;

        if (! $source || $source->faqs->isEmpty()) {
            return '';
        }

        return view('components.site.faq-list', [
            'faqs' => $source->faqs,
            'title' => $attributes['title'] ?? 'Frequently Asked Questions',
        ])->render();
    }
}
