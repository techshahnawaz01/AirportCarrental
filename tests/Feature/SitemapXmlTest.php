<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SitemapXmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_url_has_a_lastmod_matching_its_last_change(): void
    {
        $page = Page::create(['title' => 'Parking', 'slug' => 'parking', 'is_active' => true]);
        Page::withoutTimestamps(fn () => $page->forceFill(['updated_at' => Carbon::parse('2025-11-25 08:30:00')])->saveQuietly());
        Page::create(['title' => 'Map', 'slug' => 'map', 'is_active' => true]);

        $xml = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());
        $urls = collect(iterator_to_array($xml->url, false))->mapWithKeys(fn ($url) => [(string) $url->loc => (string) $url->lastmod]);

        $this->assertCount(2, $urls);
        $this->assertSame('2025-11-25T08:30:00+00:00', $urls[url('/parking')]);
        $urls->each(fn ($lastmod) => $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $lastmod));
    }

    /** @return Collection<string, string> loc => lastmod */
    private function lastmods(): Collection
    {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());

        return collect(iterator_to_array($xml->url, false))->mapWithKeys(fn ($url) => [(string) $url->loc => (string) $url->lastmod]);
    }

    private function dated(Page $page, string $updated): void
    {
        Page::withoutTimestamps(fn () => $page->forceFill(['updated_at' => Carbon::parse($updated), 'published_at' => Carbon::parse($updated)])->saveQuietly());
    }

    public function test_dates_are_written_in_the_site_timezone(): void
    {
        settings()->set(['general.timezone' => 'America/New_York']);
        $this->dated(Page::create(['title' => 'Parking', 'slug' => 'parking', 'is_active' => true]), '2026-09-30 12:36:13');

        $this->assertSame('2026-09-30T08:36:13-04:00', $this->lastmods()[url('/parking')]);
    }

    public function test_listing_pages_are_dated_by_their_newest_entry(): void
    {
        $blog = Page::create(['title' => 'Blog', 'slug' => 'blog', 'is_active' => true]);
        $home = Page::create(['title' => 'Home', 'slug' => 'home', 'is_active' => true, 'content' => '<p>Hi</p>[latest_posts]']);
        $about = Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);
        $post = Page::create(['title' => 'Sleep pods', 'slug' => 'sleep-pods', 'type' => 'post', 'parent_id' => $blog->id, 'is_active' => true]);
        $draft = Page::create(['title' => 'Draft', 'slug' => 'draft', 'type' => 'post', 'parent_id' => $blog->id, 'is_active' => false]);
        settings()->set(['general.home_page_id' => $home->id]);
        foreach ([$blog, $home, $about] as $page) {
            $this->dated($page, '2026-09-30 06:05:43');
        }
        $this->dated($post, '2026-09-30 09:35:52');
        $this->dated($draft, '2026-09-30 11:00:00');

        $urls = $this->lastmods();

        $this->assertSame('2026-09-30T09:35:52+00:00', $urls[url('/blog')]);
        $this->assertSame('2026-09-30T09:35:52+00:00', $urls[url('/').'/']);
        $this->assertSame('2026-09-30T06:05:43+00:00', $urls[url('/about')]);
    }

    public function test_imported_dates_without_a_time_stay_on_the_same_day(): void
    {
        settings()->set(['general.timezone' => 'America/New_York']);
        $this->dated(Page::create(['title' => 'Airlines', 'slug' => 'airlines', 'is_active' => true]), '2026-09-18 00:00:00');

        $this->assertSame('2026-09-18', $this->lastmods()[url('/airlines')]);
    }

    public function test_browsers_get_a_script_for_links_and_readable_dates(): void
    {
        Page::create(['title' => 'Map', 'slug' => 'map', 'is_active' => true]);

        $this->get('/sitemap.xml')->assertSee('<script xmlns="http://www.w3.org/1999/xhtml" src="'.route('sitemap.js').'"></script>', false);
        $this->get('/sitemap.js')->assertOk()->assertHeader('Content-Type', 'text/javascript; charset=UTF-8')->assertSee("createElementNS('http://www.w3.org/1999/xhtml', 'a')", false);
    }

    public function test_a_scheduled_page_is_dated_when_it_went_live(): void
    {
        $page = Page::create(['title' => 'Lounges', 'slug' => 'lounges', 'is_active' => true]);
        Page::withoutTimestamps(fn () => $page->forceFill(['updated_at' => Carbon::parse('2026-09-01 10:00:00'), 'published_at' => Carbon::parse('2026-09-05 14:00:00')])->saveQuietly());

        $this->assertSame('2026-09-05T14:00:00+00:00', $this->lastmods()[url('/lounges')]);
    }

    public function test_saving_only_faqs_updates_the_modified_date(): void
    {
        $page = Page::create(['title' => 'Wifi', 'slug' => 'wifi', 'is_active' => true]);
        Page::withoutTimestamps(fn () => $page->forceFill(['updated_at' => now()->subYear()])->saveQuietly());

        $this->actingAs($this->editor())->ajax()->put(route('admin.pages.update', $page), [
            'title' => 'Wifi', 'slug' => 'wifi', 'type' => 'page', 'template' => 'default', 'is_active' => 1,
            'faqs' => [['question' => 'Is it free?', 'answer' => 'Yes.']],
        ])->assertOk();

        $this->assertTrue($page->fresh()->updated_at->isToday());
    }

    public function test_home_page_is_listed_first_with_trailing_slash(): void
    {
        $home = Page::create(['title' => 'Home', 'slug' => 'home', 'is_active' => true]);
        Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);
        settings()->set(['general.home_page_id' => $home->id]);

        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        $this->assertSame(url('/').'/', (string) $xml->url[0]->loc);
        $this->assertSame('1.0', (string) $xml->url[0]->priority);
    }

    public function test_sitemap_links_a_css_stylesheet_for_browsers(): void
    {
        $this->get('/sitemap.xml')->assertSee('<?xml-stylesheet type="text/css" href="'.route('sitemap.css').'"?>', false);
        $this->get('/sitemap.css')->assertOk()->assertHeader('Content-Type', 'text/css; charset=UTF-8')->assertSee('lastmod::before', false);
    }
}
