<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Str;

/**
 * Builds the meta/OG/Twitter/robots/JSON-LD payload for a front-end response.
 */
class SeoService
{
    public function __construct(private readonly SettingsService $settings) {}

    public function forPage(?Page $page, array $overrides = []): array
    {
        $siteName = (string) $this->settings->get('branding.site_name');

        $title = $overrides['title']
            ?? ($page?->meta_title ?: ($page?->isHome() ? $this->settings->get('seo.meta_title', $page->title) : $page?->title))
            ?? $this->settings->get('seo.meta_title', $siteName);

        $suffix = (string) $this->settings->get('seo.title_suffix', '');
        if ($suffix !== '' && ! $page?->meta_title && ! $page?->isHome() && ! str_ends_with($title, $suffix)) {
            $title .= $suffix;
        }

        $description = $overrides['description']
            ?? ($page?->meta_description ?: ($page ? $page->summary(30) : null))
            ?: $this->settings->get('seo.meta_description', '');

        $image = $overrides['image']
            ?? $page?->ogImage?->url
            ?? $page?->featuredImage?->url
            ?? $this->settings->url('seo.og_image');

        $canonical = $overrides['canonical'] ?? ($page?->canonical_url ?: ($page ? $page->url() : url()->current()));

        $indexable = $this->settings->bool('seo.indexing_enabled') && ! ($overrides['noindex'] ?? false);
        $robots = implode(',', [
            ($indexable && ! $page?->noindex) ? 'index' : 'noindex',
            ($indexable && ! $page?->nofollow) ? 'follow' : 'nofollow',
        ]);

        return [
            'title' => Str::limit(trim(strip_tags($title)), 180, ''),
            'description' => Str::limit(trim(strip_tags(html_entity_decode((string) $description))), 300),
            'keywords' => $page?->meta_keywords ?: $this->settings->get('seo.meta_keywords'),
            'canonical' => $canonical,
            'image' => $image,
            'robots' => $robots,
            'type' => $page?->type === 'post' ? 'article' : 'website',
            'site_name' => $siteName,
            'schema' => $page ? $this->schema($page, $canonical, $image) : [],
        ];
    }

    private function schema(Page $page, string $url, ?string $image): array
    {
        $siteName = $this->settings->get('branding.site_name');
        $graph = [];

        if ($page->isHome()) {
            $graph[] = [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => url('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('search').'?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ];
        }

        if ($page->type === 'post') {
            $graph[] = array_filter([
                '@type' => 'Article',
                'headline' => $page->title,
                'image' => $image,
                'datePublished' => optional($page->published_at ?? $page->created_at)->toIso8601String(),
                'dateModified' => optional($page->updated_at)->toIso8601String(),
                'author' => ['@type' => 'Organization', 'name' => $siteName],
                'mainEntityOfPage' => $url,
            ]);
        }

        if ($page->type === 'hotel') {
            $graph[] = array_filter([
                '@type' => 'Hotel',
                'name' => $page->title,
                'url' => $url,
                'image' => $image,
                'address' => $page->meta('address') ? ['@type' => 'PostalAddress', 'streetAddress' => $page->meta('address')] : null,
            ]);
        }

        if (! $page->isHome()) {
            $crumbs = $page->ancestors()->push($page)->prepend(null);
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $crumbs->values()->map(fn ($crumb, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb ? $crumb->title : 'Home',
                    'item' => $crumb ? $crumb->url() : url('/'),
                ])->all(),
            ];
        }

        if ($page->relationLoaded('faqs') && $page->faqs->isNotEmpty()) {
            $graph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $page->faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->answer)],
                ])->all(),
            ];
        }

        return $graph ? ['@context' => 'https://schema.org', '@graph' => $graph] : [];
    }
}
