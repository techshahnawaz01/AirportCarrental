<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopBarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);
    }

    public function test_top_bar_shows_the_text_from_settings(): void
    {
        app(SettingsService::class)->set(['branding.top_bar_text' => 'Welcome aboard the CLT guide', 'branding.tagline' => 'Tagline only']);

        $this->get('/about')->assertOk()->assertSee('container-site flex h-10', false)->assertSee('Welcome aboard the CLT guide');
    }

    public function test_top_bar_is_hidden_when_it_has_nothing_to_show(): void
    {
        app(SettingsService::class)->set([
            'branding.top_bar_text' => '',
            'contact.phone' => '', 'general.phone' => '', 'contact.contact_email' => '', 'general.site_email' => '',
        ]);

        $this->get('/about')->assertOk()->assertDontSee('container-site flex h-10', false);
    }
}
