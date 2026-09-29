<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_sitemap_groups_pages_by_section_and_hides_unindexed_pages(): void
    {
        $airlines = Page::create(['title' => 'Airlines', 'slug' => 'airlines', 'is_active' => true]);
        Page::create(['title' => 'Delta', 'slug' => 'delta', 'parent_id' => $airlines->id, 'is_active' => true]);
        Page::create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'is_active' => true]);
        Page::create(['title' => 'Secret Draft', 'slug' => 'draft', 'is_active' => false]);
        Page::create(['title' => 'Hidden From Google', 'slug' => 'hidden', 'is_active' => true, 'noindex' => true]);

        $this->get('/sitemap')->assertOk()
            ->assertSee('id="section-'.$airlines->id.'"', false)
            ->assertSeeInOrder(['Airlines', 'Delta'])
            ->assertSee('General pages')
            ->assertSee('Privacy Policy')
            ->assertDontSee('Secret Draft')
            ->assertDontSee('Hidden From Google')
            ->assertSee(route('sitemap'), false);
    }

    public function test_sitemap_slug_is_reserved(): void
    {
        $this->actingAs($this->editor())->ajax()->post('/admin/pages', ['title' => 'Sitemap', 'type' => 'page', 'template' => 'default'])
            ->assertStatus(422)->assertJsonValidationErrors('slug');
    }
}
