<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_seeded_site_renders_its_main_pages(): void
    {
        $this->get('/')->assertOk()->assertSee('Miami International Airport')->assertSee('Frequently Asked Questions');

        foreach (['flight-arrivals', 'flight-departures', 'flights/delays-and-cancellations', 'wait-time', 'transportation', 'blog', 'hotels', 'contact-us', 'privacy-policy', 'disclaimer'] as $path) {
            $this->get('/'.$path)->assertOk();
        }
    }

    public function test_home_page_path_redirects_to_root_and_legacy_redirects_work(): void
    {
        $this->get('/home')->assertRedirect('/');
        $this->get('/wait-times')->assertRedirect('/wait-time')->assertStatus(301);
    }

    public function test_seo_endpoints(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('<loc>'.url('/').'/</loc>', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
        $this->get('/transportation')->assertSee('<link rel="canonical" href="'.url('/transportation').'">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_theme_colours_are_output_as_css_variables(): void
    {
        $this->get('/')->assertSee('--brand-primary:#06519c', false);
    }

    public function test_every_menu_item_is_linked(): void
    {
        $this->assertGreaterThan(0, Page::count());
        $this->get('/')->assertSee('Transportation')->assertSee('Privacy Policy');
    }
}
