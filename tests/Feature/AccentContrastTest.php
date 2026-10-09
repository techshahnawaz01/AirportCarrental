<?php

namespace Tests\Feature;

use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccentContrastTest extends TestCase
{
    use RefreshDatabase;

    public function test_light_accents_get_dark_text_and_a_deep_ink_shade(): void
    {
        app(SettingsService::class)->set(['theme.accent_color' => '#fdb72a']);

        $css = app(SettingsService::class)->cssVariables();

        $this->assertStringContainsString('--brand-on-accent:#1a2332', $css);
        $this->assertStringContainsString('--brand-accent-ink:#725213', $css);
    }

    public function test_dark_accents_keep_white_text(): void
    {
        app(SettingsService::class)->set(['theme.accent_color' => '#ff7203']);

        $css = app(SettingsService::class)->cssVariables();

        $this->assertStringContainsString('--brand-on-accent:#ffffff', $css);
        $this->assertStringNotContainsString('--brand-accent-ink', $css);
    }
}
