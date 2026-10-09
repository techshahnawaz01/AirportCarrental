<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IconSpriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_reference_icons_from_one_sprite(): void
    {
        Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);

        $html = $this->get('/about')->assertOk()->getContent();

        preg_match_all('/<use href="#icon-([a-z-]+)"/', $html, $uses);
        preg_match_all('/<symbol id="icon-([a-z-]+)"/', $html, $symbols);

        $this->assertNotEmpty($uses[1]);
        // Every referenced icon is defined exactly once.
        $this->assertEqualsCanonicalizing(array_unique($uses[1]), $symbols[1]);
    }

    public function test_admin_screens_keep_inline_icons(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        $this->assertStringNotContainsString('<use href="#icon-', $html);
        $this->assertStringContainsString('<path d="', $html);
    }
}
