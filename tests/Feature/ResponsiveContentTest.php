<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_tables_get_a_scroll_box_but_shortcode_tables_do_not(): void
    {
        Page::create(['title' => 'Rates', 'slug' => 'rates', 'is_active' => true,
            'content' => '<table><tr><td>Daily</td><td>USD 25</td></tr></table>[flight_board type="arrivals"]']);

        $html = $this->get('/rates')->assertOk()->getContent();

        $this->assertStringContainsString('<div class="table-scroll"><table><tr><td>Daily</td>', $html);
        $this->assertSame(1, substr_count($html, 'class="table-scroll"'));
    }

    public function test_breadcrumbs_use_the_site_name_and_slug_labels(): void
    {
        app(SettingsService::class)->set(['branding.site_name' => 'CLT Guide']);
        $flights = Page::create(['title' => 'All Flights at CLT', 'slug' => 'flights', 'is_active' => true]);
        Page::create(['title' => 'CLT Flight Delays and Cancellations', 'slug' => 'delays-and-cancellations', 'parent_id' => $flights->id, 'is_active' => true]);

        $this->get('/flights/delays-and-cancellations')->assertOk()
            ->assertSeeInOrder(['aria-label="Breadcrumb"', 'CLT Guide', 'Flights', 'Delays and cancellations'], false);
    }
}
