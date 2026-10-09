<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private const CONTENT = '[call_cta title="Stuck?"][card title="Taxi"]<p>Ride.</p>[/card][flight_disruptions]';

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['title' => 'Help', 'slug' => 'help', 'is_active' => true, 'content' => self::CONTENT]);
        Page::create(['title' => 'Contact', 'slug' => 'contact-us', 'template' => 'contact', 'is_active' => true]);
    }

    public function test_no_phone_number_anywhere_when_settings_are_empty(): void
    {
        app(SettingsService::class)->set(['contact.phone' => '', 'general.phone' => '', 'contact.show_call_bar' => 1]);

        foreach (['/help', '/contact-us', '/search'] as $path) {
            $this->get($path)->assertOk()
                ->assertDontSee('tel:', false)
                ->assertDontSee('Call now')
                ->assertDontSee('Call to book')
                ->assertDontSee('Need help with your trip?');
        }
    }

    public function test_phone_number_appears_everywhere_once_set(): void
    {
        app(SettingsService::class)->set(['contact.phone' => '+1-800-555-0100', 'contact.show_call_bar' => 1]);

        $this->get('/help')->assertOk()
            ->assertSee('href="tel:+18005550100"', false)
            ->assertSee('Stuck?')
            ->assertSee('Call to book')
            ->assertSee('data-phone="+1-800-555-0100"', false);
    }

    public function test_general_phone_is_used_when_contact_phone_is_empty(): void
    {
        app(SettingsService::class)->set(['contact.phone' => '', 'general.phone' => '+1-800-555-0199']);

        $this->get('/help')->assertOk()->assertSee('href="tel:+18005550199"', false);
    }
}
