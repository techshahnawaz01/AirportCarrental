<?php

namespace App\Shortcodes;

use App\Models\Page;

class ChildPagesShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $parent = isset($attributes['parent']) ? Page::where('path', trim($attributes['parent'], '/'))->first() : $page;
        if (! $parent) {
            return '';
        }

        $pages = $parent->children()->published()->with('featuredImage')
            ->orderBy('sort_order')->latest('published_at')->latest('id')
            ->limit(min((int) ($attributes['limit'] ?? 12), 48))
            ->get();

        return view('frontend.shortcodes.page-grid', [
            'pages' => $pages,
            'title' => $attributes['title'] ?? null,
        ])->render();
    }
}
