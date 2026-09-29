<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $pages = Page::published()
            ->where('noindex', false)
            ->whereNull('canonical_url')
            ->orderBy('path')
            ->get(['id', 'path', 'type', 'updated_at', 'sitemap_priority']);

        return response()
            ->view('frontend.seo.sitemap', ['pages' => $pages], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = settings()->bool('seo.indexing_enabled')
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /search', 'Disallow: /widgets']
            : ['User-agent: *', 'Disallow: /'];

        if ($extra = trim((string) settings('seo.robots_txt'))) {
            $lines[] = '';
            $lines[] = $extra;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
