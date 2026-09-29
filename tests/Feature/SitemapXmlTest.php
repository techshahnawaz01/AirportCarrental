<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
