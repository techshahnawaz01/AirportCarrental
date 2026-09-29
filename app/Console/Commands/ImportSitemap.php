<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use App\Services\HtmlSanitizer;
use App\Services\MediaService;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Migrates an existing website into the CMS by crawling its XML sitemap:
 * titles, SEO meta, main content, FAQs, featured images and inline images.
 *
 *   php artisan cms:import-sitemap https://example.com/sitemap.xml
 */
class ImportSitemap extends Command
{
    protected $signature = 'cms:import-sitemap
        {sitemap : URL of the XML sitemap}
        {--selector=.content-area,.entry-content,#reach,article,main : Comma separated .classes, #ids or tags holding the main content, tried in order}
        {--update : Overwrite pages that already exist}
        {--only= : Only import paths starting with this prefix}
        {--limit=0 : Stop after this many pages}
        {--without-images : Do not download images}';

    protected $description = 'Import pages, meta data, FAQs and images from another website’s sitemap';

    private string $host;

    private ?int $authorId;

    /** @var array<string, ?Media> */
    private array $downloaded = [];

    public function __construct(private readonly MediaService $media, private readonly HtmlSanitizer $sanitizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $sitemap = $this->argument('sitemap');
        $this->host = (string) parse_url($sitemap, PHP_URL_HOST);
        $this->authorId = User::where('role', User::ROLE_ADMIN)->value('id');

        $entries = $this->readSitemap($sitemap);
        if ($entries === null) {
            return self::FAILURE;
        }

        $allPaths = array_keys($entries);
        if ($prefix = trim((string) $this->option('only'), '/')) {
            $entries = array_filter($entries, fn ($_, $path) => str_starts_with($path, $prefix), ARRAY_FILTER_USE_BOTH);
        }

        // Parents before children so hierarchical URLs are preserved.
        uksort($entries, fn ($a, $b) => substr_count($a, '/') <=> substr_count($b, '/') ?: strcmp($a, $b));
        if ($limit = (int) $this->option('limit')) {
            $entries = array_slice($entries, 0, $limit, true);
        }

        $this->components->info(count($entries).' URLs to process from '.$this->host);
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($entries as $path => $lastmod) {
            $existing = Page::withTrashed()->where('path', $path)->first();
            $isHome = $existing && (int) settings('general.home_page_id') === $existing->id;

            if ($path === '' || $isHome || ($existing && ! $this->option('update'))) {
                $stats['skipped']++;
                $this->components->twoColumnDetail('/'.$path, '<fg=gray>skipped</>');

                continue;
            }

            try {
                $this->importPage($path, $lastmod, $existing, $allPaths);
                $stats[$existing ? 'updated' : 'created']++;
                $this->components->twoColumnDetail('/'.$path, $existing ? '<fg=yellow>updated</>' : '<fg=green>created</>');
            } catch (Throwable $e) {
                $stats['failed']++;
                $this->components->twoColumnDetail('/'.$path, '<fg=red>failed: '.Str::limit($e->getMessage(), 80).'</>');
            }
        }

        $linked = $this->linkMenuItems();

        $this->newLine();
        $this->components->info(sprintf('Done. %d created, %d updated, %d skipped, %d failed. %d menu links connected to pages.',
            $stats['created'], $stats['updated'], $stats['skipped'], $stats['failed'], $linked));

        return self::SUCCESS;
    }

    /**
     * @return array<string, ?string>|null path => lastmod
     */
    private function readSitemap(string $url): ?array
    {
        try {
            $xml = Http::timeout(30)->get($url)->throw()->body();
        } catch (Throwable $e) {
            $this->components->error('Could not download the sitemap: '.$e->getMessage());

            return null;
        }

        $document = @simplexml_load_string($xml);
        if (! $document) {
            $this->components->error('The sitemap is not valid XML.');

            return null;
        }

        $entries = [];
        foreach ($document->url as $node) {
            $loc = (string) $node->loc;
            if (parse_url($loc, PHP_URL_HOST) !== $this->host) {
                continue;
            }
            $entries[trim((string) parse_url($loc, PHP_URL_PATH), '/')] = (string) $node->lastmod ?: null;
        }

        return $entries;
    }

    private function importPage(string $path, ?string $lastmod, ?Page $existing, array $allPaths): void
    {
        $html = Http::timeout(30)->withHeaders(['User-Agent' => 'CMS importer'])->get('https://'.$this->host.'/'.$path.'/')->throw()->body();

        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($document);

        $segments = explode('/', $path);
        $slug = array_pop($segments);
        $parent = $segments ? $this->ensureParent(implode('/', $segments)) : null;
        $type = str_starts_with($path, 'blog/') ? 'post' : (str_starts_with($path, 'hotels/') ? 'hotel' : 'page');
        $hasChildren = collect($allPaths)->contains(fn ($p) => str_starts_with($p, $path.'/'));

        $heading = $this->text($xpath, '//*[contains(@class,"heading-title")]') ?: $this->text($xpath, '//h1');
        $metaTitle = $this->text($xpath, '//title');
        $contentNode = $this->contentNode($xpath);
        $data = $type === 'hotel' ? $this->hotelData($xpath) : null;
        $faqs = $this->faqs($xpath);

        $heroImage = null;
        foreach ($xpath->query('//*[contains(@class,"new-feature")]') as $hero) {
            if (preg_match("/url\\(['\"]?([^'\")]+)/", $hero->getAttribute('style'), $m)) {
                $heroImage = $this->download($m[1]);
            }
        }
        $ogImage = $this->meta($xpath, 'og:image', 'property');
        $featured = $heroImage ?? ($ogImage ? $this->download($ogImage) : null);

        $content = $contentNode ? $this->cleanContent($document, $contentNode, $path) : '';

        $page = $existing ?? new Page;
        if ($page->trashed()) {
            $page->restore();
        }

        $page->fill([
            'parent_id' => $parent?->id,
            'author_id' => $page->author_id ?? $this->authorId,
            'type' => $type,
            'template' => $type === 'hotel' ? 'hotel' : ($type === 'post' ? 'post' : ($hasChildren ? 'listing' : 'default')),
            'title' => Str::limit($heading ?: Str::headline($slug), 250, ''),
            'slug' => $slug,
            'content' => $content,
            'data' => $data,
            'featured_image_id' => $featured?->id,
            'meta_title' => $metaTitle && $metaTitle !== $heading ? Str::limit($metaTitle, 250, '') : null,
            'meta_description' => $this->meta($xpath, 'description'),
            'meta_keywords' => $this->meta($xpath, 'keywords'),
            'noindex' => str_contains((string) $this->meta($xpath, 'robots'), 'noindex'),
            'nofollow' => str_contains((string) $this->meta($xpath, 'robots'), 'nofollow'),
            'is_active' => true,
            'allow_comments' => $type === 'post',
            'published_at' => $page->published_at ?? ($lastmod ? Carbon::parse($lastmod) : now()),
        ])->save();

        $page->faqs()->delete();
        foreach ($faqs as $index => [$question, $answer]) {
            $page->faqs()->create(['question' => $question, 'answer' => $answer, 'sort_order' => $index]);
        }

        // Keep the original site's modified date so the new sitemap reports real <lastmod> values.
        if ($lastmod) {
            Page::withoutTimestamps(fn () => $page->forceFill(['updated_at' => Carbon::parse($lastmod)])->saveQuietly());
        }
    }

    private function ensureParent(string $path): Page
    {
        $page = Page::withTrashed()->where('path', $path)->first();
        if ($page) {
            return $page;
        }

        $segments = explode('/', $path);
        $slug = array_pop($segments);

        return Page::create([
            'parent_id' => $segments ? $this->ensureParent(implode('/', $segments))->id : null,
            'author_id' => $this->authorId,
            'title' => Str::headline($slug),
            'slug' => $slug,
            'template' => 'listing',
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    private function contentNode(DOMXPath $xpath): ?DOMNode
    {
        foreach (explode(',', (string) $this->option('selector')) as $selector) {
            $selector = trim($selector);
            $query = match ($selector[0] ?? '') {
                '.' => '//*[contains(concat(" ", normalize-space(@class), " "), " '.substr($selector, 1).' ")]',
                '#' => '//*[@id="'.substr($selector, 1).'"]',
                default => '//'.$selector,
            };
            if ($node = $xpath->query($query)->item(0)) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Strip presentation markup, rebuild known widgets as shortcodes, localise images and links.
     */
    private function cleanContent(DOMDocument $document, DOMNode $node, string $path): string
    {
        $xpath = new DOMXPath($document);

        foreach (iterator_to_array($xpath->query('.//script|.//style|.//noscript|.//*[contains(@class,"section-faqs")]|.//*[contains(@class,"faq-container")]|.//aside', $node)) as $remove) {
            $remove->parentNode?->removeChild($remove);
        }

        // Call-to-action widgets become the reusable [call_cta] block (phone comes from settings).
        foreach (iterator_to_array($xpath->query('.//*[contains(@class,"intract_help-module") or contains(@class,"sidebar_cta-container")]', $node)) as $cta) {
            $texts = [];
            foreach ($xpath->query('.//p', $cta) as $p) {
                $texts[] = trim($p->textContent);
            }
            $shortcode = sprintf('[call_cta title="%s" text="%s"]', $this->attr($texts[0] ?? 'Need help with your trip?'), $this->attr($texts[1] ?? ''));
            $cta->parentNode?->replaceChild($document->createTextNode($shortcode), $cta);
        }

        // Legacy car rental cards become [card] blocks.
        foreach (iterator_to_array($xpath->query('.//*[contains(@class,"block-car_rental_card")]', $node)) as $card) {
            $title = $this->attr(trim((string) $xpath->query('.//*[contains(@class,"card-title")]', $card)->item(0)?->textContent));
            $image = $xpath->query('.//img', $card)->item(0);
            $media = $image ? $this->download($image->getAttribute('src')) : null;
            $body = '';
            foreach (iterator_to_array($xpath->query('.//*[contains(@class,"card-body")]/*[not(contains(@class,"card-title")) and not(self::a)]', $card)) as $child) {
                $body .= $document->saveHTML($child);
            }
            $shortcode = '[card title="'.$title.'"'.($media ? ' image="'.$media->path.'"' : '').']'.$body.'[/card]';
            $card->parentNode?->replaceChild($document->createTextNode($shortcode), $card);
        }

        foreach (iterator_to_array($xpath->query('.//img', $node)) as $img) {
            /** @var DOMElement $img */
            $src = $img->getAttribute('data-src') ?: $img->getAttribute('src');
            $media = $src ? $this->download($src) : null;
            if (! $media) {
                $img->parentNode?->removeChild($img);

                continue;
            }
            $alt = $img->getAttribute('alt');
            foreach (iterator_to_array($img->attributes) as $attribute) {
                $img->removeAttribute($attribute->name);
            }
            $img->setAttribute('src', $media->relativeUrl());
            $img->setAttribute('alt', $alt ?: (string) $media->alt);
            $img->setAttribute('loading', 'lazy');
            if ($media->width) {
                $img->setAttribute('width', (string) $media->width);
                $img->setAttribute('height', (string) $media->height);
            }
        }

        foreach (iterator_to_array($xpath->query('.//a', $node)) as $link) {
            /** @var DOMElement $link */
            $link->setAttribute('href', $this->localLink($link->getAttribute('href'), $path));
        }

        foreach (iterator_to_array($xpath->query('.//*', $node)) as $element) {
            /** @var DOMElement $element */
            foreach (['class', 'style', 'id', 'itemprop', 'itemscope', 'itemtype', 'data-bs-toggle', 'data-bs-target', 'role'] as $attribute) {
                $element->removeAttribute($attribute);
            }
        }

        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        // Unwrap layout-only containers and drop empty paragraphs.
        $html = preg_replace(['#</?(div|span|section|figure|font|center)[^>]*>#i', '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '#\n{3,}#'], ['', '', "\n\n"], $html);

        return $this->sanitizer->clean($html);
    }

    private function faqs(DOMXPath $xpath): array
    {
        $faqs = [];
        foreach ($xpath->query('//*[contains(@class,"accordion-item")]') as $item) {
            $question = trim((string) $xpath->query('.//*[contains(@class,"accordion-button")]', $item)->item(0)?->textContent);
            $answerNode = $xpath->query('.//*[contains(@class,"accordion-body")]', $item)->item(0);
            if ($question && $answerNode) {
                $faqs[] = [$question, $this->sanitizer->clean($this->innerHtml($answerNode))];
            }
        }
        foreach ($xpath->query('//*[contains(@class,"faq-item")]') as $item) {
            $question = trim((string) $xpath->query('.//*[contains(@class,"faq-question")]', $item)->item(0)?->textContent);
            $answerNode = $xpath->query('.//*[contains(@class,"faq-answer-content")]', $item)->item(0);
            if ($question && $answerNode) {
                $faqs[] = [$question, $this->sanitizer->clean($this->innerHtml($answerNode))];
            }
        }

        return collect($faqs)->unique(fn ($faq) => $faq[0])->values()->all();
    }

    private function hotelData(DOMXPath $xpath): ?array
    {
        $gallery = [];
        foreach ($xpath->query('//section[@id="gallery"]//img') as $img) {
            if ($media = $this->download($img->getAttribute('src'))) {
                $gallery[] = $media->id;
            }
        }

        $rooms = [];
        foreach ($xpath->query('//section[@id="rooms"]//*[contains(@class,"custom-card")]') as $card) {
            $image = $xpath->query('.//img', $card)->item(0);
            $rooms[] = [
                'title' => trim((string) $xpath->query('.//*[contains(@class,"card-title")]', $card)->item(0)?->textContent),
                'description' => trim(preg_replace('/\s+/', ' ', (string) $xpath->query('.//*[contains(@class,"card-text")]', $card)->item(0)?->textContent)),
                'image_id' => $image ? $this->download($image->getAttribute('src'))?->id : null,
                'amenities' => collect(iterator_to_array($xpath->query('.//li', $card)))->map(fn ($li) => trim($li->textContent))->filter()->values()->all(),
            ];
        }

        $location = $xpath->query('//section[@id="location"]')->item(0);
        $locationTitle = $location ? trim((string) $xpath->query('.//h2', $location)->item(0)?->textContent) : null;
        $description = $location ? $xpath->query('.//*[contains(@class,"col-lg-6")][1]', $location)->item(0) : null;
        if ($description) {
            foreach (iterator_to_array($xpath->query('.//h2', $description)) as $h2) {
                $h2->parentNode->removeChild($h2);
            }
        }
        $descriptionHtml = $description ? $this->sanitizer->clean(preg_replace('# (class|style)="[^"]*"#', '', $this->innerHtml($description))) : null;
        $address = null;
        if ($descriptionHtml && preg_match('/Address\s*:\s*([^<\n]+)/i', strip_tags($descriptionHtml, '<br>'), $m)) {
            $address = trim($m[1]);
        }

        $data = array_filter([
            'gallery' => $gallery,
            'rooms' => array_values(array_filter($rooms, fn ($r) => $r['title'] !== '')),
            'location_title' => $locationTitle,
            'location_description' => $descriptionHtml,
            'map_embed_url' => $location ? $xpath->query('.//iframe', $location)->item(0)?->getAttribute('src') : null,
            'address' => $address,
        ]);

        return $data ?: null;
    }

    private function download(string $src): ?Media
    {
        if ($this->option('without-images') || str_starts_with($src, 'data:')) {
            return null;
        }

        $url = str_starts_with($src, '//') ? 'https:'.$src : (str_starts_with($src, 'http') ? $src : 'https://'.$this->host.'/'.ltrim($src, '/'));
        $url = str_replace(' ', '%20', html_entity_decode($url));

        if (array_key_exists($url, $this->downloaded)) {
            return $this->downloaded[$url];
        }

        try {
            $response = Http::timeout(30)->get($url);
            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                return $this->downloaded[$url] = null;
            }

            $name = urldecode(basename((string) parse_url($url, PHP_URL_PATH))) ?: 'image.jpg';
            $temp = tempnam(sys_get_temp_dir(), 'cms');
            file_put_contents($temp, $response->body());
            if (! @getimagesize($temp)) {
                @unlink($temp);

                return $this->downloaded[$url] = null;
            }
            $media = $this->media->importFromPath($temp, $name);
            @unlink($temp);

            return $this->downloaded[$url] = $media;
        } catch (Throwable) {
            return $this->downloaded[$url] = null;
        }
    }

    /**
     * Connect menu items that point at a URL path to the page now living there.
     */
    private function linkMenuItems(): int
    {
        $linked = 0;
        MenuItem::whereNull('page_id')->whereNotNull('url')->where('url', 'like', '/%')->each(function (MenuItem $item) use (&$linked) {
            if ($page = Page::where('path', trim($item->url, '/'))->first()) {
                $item->update(['page_id' => $page->id, 'url' => null]);
                $linked++;
            }
        });

        return $linked;
    }

    /**
     * Same-site links become clean root-relative paths ("../../taxi/" on /hotels/x → "/taxi").
     */
    private function localLink(string $href, string $pagePath): string
    {
        if ($href === '' || preg_match('#^(\#|mailto:|tel:|javascript:)#i', $href)) {
            return $href;
        }

        $host = parse_url($href, PHP_URL_HOST);
        if ($host && $host !== $this->host) {
            return $href;
        }

        $path = (string) parse_url($href, PHP_URL_PATH);
        if (! $host && ! str_starts_with($path, '/')) {
            $path = $pagePath.'/'.$path; // relative to the page's own trailing-slash URL
        }

        $resolved = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                array_pop($resolved);
            } elseif ($segment !== '' && $segment !== '.') {
                $resolved[] = $segment;
            }
        }

        $fragment = parse_url($href, PHP_URL_FRAGMENT);

        return '/'.implode('/', $resolved).($fragment ? '#'.$fragment : '');
    }

    private function text(DOMXPath $xpath, string $query): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $xpath->query($query)->item(0)?->textContent));

        return $text !== '' ? html_entity_decode($text) : null;
    }

    private function meta(DOMXPath $xpath, string $name, string $attribute = 'name'): ?string
    {
        $value = trim((string) $xpath->query("//meta[@{$attribute}=\"{$name}\"]")->item(0)?->getAttribute('content'));

        return $value !== '' ? html_entity_decode($value) : null;
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return trim($html);
    }

    private function attr(string $value): string
    {
        return str_replace(['"', '[', ']'], ['″', '(', ')'], trim(preg_replace('/\s+/', ' ', $value)));
    }
}
