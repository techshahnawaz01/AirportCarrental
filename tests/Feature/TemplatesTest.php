<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\TableOfContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_template_builds_a_table_of_contents(): void
    {
        Page::create(['title' => 'Parking Guide', 'slug' => 'parking', 'template' => 'guide', 'is_active' => true,
            'content' => '<h2>Garages</h2><p>Two garages.</p><h3>Dolphin</h3><p>D and E.</p><h2>Economy lots</h2><p>Cheap.</p>']);

        $this->get('/parking')->assertOk()
            ->assertSee('On this page')
            ->assertSee('<h2 id="garages">Garages</h2>', false)
            ->assertSee('href="#economy-lots"', false)
            ->assertSee('data-reading-progress', false);
    }

    public function test_table_of_contents_ids_are_unique(): void
    {
        [$html, $items] = TableOfContents::build('<h2>Tips</h2><h2>Tips</h2><h2 id="custom">Other</h2>');

        $this->assertSame(['tips', 'tips-2', 'custom'], array_column($items, 'id'));
        $this->assertStringContainsString('<h2 id="tips-2">Tips</h2>', $html);
    }

    public function test_landing_template_shows_configured_buttons(): void
    {
        $this->actingAs($this->editor())->ajax()->post('/admin/pages', [
            'title' => 'Travel Help', 'type' => 'page', 'template' => 'landing', 'is_active' => 1,
            'excerpt' => 'Real agents, 24/7.',
            'data' => ['hero' => ['eyebrow' => 'Always on', 'primary_label' => 'Call now', 'primary_url' => 'tel:+18005550100', 'secondary_label' => 'Read more', 'secondary_url' => '#more']],
        ])->assertCreated();

        $this->get('/travel-help')->assertOk()
            ->assertSee('Always on')->assertSee('Real agents, 24/7.')
            ->assertSee('href="tel:+18005550100"', false)->assertSee('Read more');
    }

    public function test_landing_buttons_reject_unsafe_links(): void
    {
        $this->actingAs($this->editor())->ajax()->post('/admin/pages', [
            'title' => 'Bad', 'type' => 'page', 'template' => 'landing',
            'data' => ['hero' => ['primary_label' => 'Click', 'primary_url' => 'javascript:alert(1)']],
        ])->assertStatus(422)->assertJsonValidationErrors('data.hero.primary_url');
    }

    public function test_directory_template_lists_children_alphabetically_with_filter(): void
    {
        $parent = Page::create(['title' => 'Airlines', 'slug' => 'airlines', 'template' => 'directory', 'is_active' => true]);
        foreach (['Delta', 'American', 'Copa'] as $name) {
            Page::create(['title' => $name, 'slug' => strtolower($name), 'parent_id' => $parent->id, 'is_active' => true]);
        }

        $this->get('/airlines')->assertOk()
            ->assertSee('data-directory-search', false)
            ->assertSeeInOrder(['American', 'Copa', 'Delta'])
            ->assertSee('href="#letter-A"', false);
    }

    public function test_magazine_template_features_the_latest_post(): void
    {
        $blog = Page::create(['title' => 'Blog', 'slug' => 'blog', 'template' => 'magazine', 'is_active' => true]);
        Page::create(['title' => 'Older story', 'slug' => 'older', 'type' => 'post', 'parent_id' => $blog->id, 'is_active' => true, 'published_at' => now()->subDays(3)]);
        Page::create(['title' => 'Newest story', 'slug' => 'newest', 'type' => 'post', 'parent_id' => $blog->id, 'is_active' => true, 'published_at' => now()->subDay()]);

        $this->get('/blog')->assertOk()->assertSee('Latest story')->assertSeeInOrder(['Newest story', 'Older story']);
    }

    public function test_every_registered_template_renders(): void
    {
        foreach (array_keys(config('cms.templates')) as $template) {
            Page::create(['title' => "T {$template}", 'slug' => "t-{$template}", 'template' => $template, 'is_active' => true, 'content' => '<h2>A</h2><p>b</p><h2>C</h2>']);
            $this->get("/t-{$template}")->assertOk();
        }
    }
}
