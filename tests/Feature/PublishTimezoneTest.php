<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublishTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingsService::class)->set(['general.timezone' => 'America/New_York']);
        Carbon::setTestNow('2026-09-30 09:00:00'); // 05:00 in New York
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + ['title' => 'Sleep pods', 'slug' => 'sleep-pods', 'type' => 'page', 'template' => 'default', 'is_active' => '1'];
    }

    public function test_publish_date_is_read_in_the_site_timezone(): void
    {
        $this->actingAs($this->admin())->ajax()->post('/admin/pages', $this->payload(['published_at' => '2026-09-30T04:30']))->assertCreated();

        // 04:30 New York = 08:30 UTC, i.e. already in the past, so the page is live.
        $this->assertSame('2026-09-30 08:30:00', Page::first()->published_at->utc()->toDateTimeString());
        $this->get('/sleep-pods')->assertOk();
    }

    public function test_publish_date_is_read_in_the_editors_own_timezone(): void
    {
        // An editor in India types 14:25 IST, which is 08:55 UTC: already past, so it goes live now.
        $this->actingAs($this->admin())->ajax()->post('/admin/pages', $this->payload(['published_at' => '2026-09-30T14:25', 'client_timezone' => 'Asia/Kolkata']))
            ->assertCreated();

        $this->assertSame('2026-09-30 08:55:00', Page::first()->published_at->utc()->toDateTimeString());
        auth()->logout();
        $this->get('/sleep-pods')->assertOk();
    }

    public function test_legacy_or_unknown_browser_timezones_are_accepted(): void
    {
        $admin = $this->admin();

        // Some browsers in India report the legacy name Asia/Calcutta.
        $this->actingAs($admin)->ajax()->post('/admin/pages', $this->payload(['published_at' => '2026-09-30T14:25', 'client_timezone' => 'Asia/Calcutta']))->assertCreated();
        $this->assertSame('2026-09-30 08:55:00', Page::first()->published_at->utc()->toDateTimeString());

        // Anything unrecognised falls back to the site timezone instead of failing validation.
        $this->ajax()->post('/admin/pages', $this->payload(['slug' => 'other', 'published_at' => '2026-09-30T04:30', 'client_timezone' => 'Mars/Olympus']))->assertCreated();
        $this->assertSame('2026-09-30 08:30:00', Page::where('slug', 'other')->first()->published_at->utc()->toDateTimeString());
    }

    public function test_future_dates_are_reported_as_scheduled(): void
    {
        $admin = $this->admin();
        $page = Page::create(['title' => 'Sleep pods', 'slug' => 'sleep-pods', 'is_active' => true, 'published_at' => now()]);

        $this->actingAs($admin)->ajax()->put("/admin/pages/{$page->id}", $this->payload(['published_at' => '2026-09-30T14:33']))
            ->assertOk()->assertJsonFragment(['message' => 'Page saved. Scheduled to go live Sep 30, 2026 2:33 PM (your time) – not visible until then.']);
        $this->get('/admin/pages')->assertSee('Scheduled');

        // Visitors (not signed-in staff, who get a preview) don't see it yet.
        auth()->logout();
        $this->flushHeaders()->get('/sleep-pods')->assertNotFound();
    }

    public function test_publishing_from_the_list_goes_live_immediately(): void
    {
        $page = Page::create(['title' => 'Sleep pods', 'slug' => 'sleep-pods', 'is_active' => false, 'published_at' => now()->addHours(5)]);

        $this->actingAs($this->admin())->ajax()->patch("/admin/pages/{$page->id}/toggle")->assertOk();

        $this->assertTrue($page->fresh()->isPublished());
        $this->get('/sleep-pods')->assertOk();
    }
}
