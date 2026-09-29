<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return $overrides + ['title' => 'Terminal Guide', 'type' => 'page', 'template' => 'default', 'is_active' => 1];
    }

    public function test_create_page_with_faqs_image_and_sanitised_content(): void
    {
        $this->actingAs($this->editor())->ajax()->post('/admin/pages', $this->payload([
            'content' => '<p>Hi<script>alert(1)</script><a href="javascript:x()" onclick="y()">x</a></p>',
            'faqs' => [['question' => 'Open 24/7?', 'answer' => 'Yes.']],
            'featured_image_upload' => UploadedFile::fake()->image('terminal.jpg', 1200, 800),
        ]))->assertCreated()->assertJsonPath('success', true);

        $page = Page::firstOrFail();
        $this->assertSame('terminal-guide', $page->path);
        $this->assertStringNotContainsString('script', $page->content);
        $this->assertStringNotContainsString('javascript:', $page->content);
        $this->assertStringNotContainsString('onclick', $page->content);
        $this->assertCount(1, $page->faqs);
        $this->assertNotNull($page->featured_image_id);
    }

    public function test_slug_conflicts_and_reserved_paths_are_rejected(): void
    {
        $user = $this->editor();
        Page::create(['title' => 'Terminal Guide', 'slug' => 'terminal-guide']);

        $this->actingAs($user)->ajax()->post('/admin/pages', $this->payload())->assertStatus(422)->assertJsonValidationErrors('slug');
        $this->actingAs($user)->ajax()->post('/admin/pages', $this->payload(['title' => 'Admin']))->assertStatus(422)->assertJsonValidationErrors('slug');
    }

    public function test_moving_a_page_updates_descendant_urls(): void
    {
        $a = Page::create(['title' => 'A', 'slug' => 'a']);
        $b = Page::create(['title' => 'B', 'slug' => 'b', 'parent_id' => $a->id]);
        $c = Page::create(['title' => 'C', 'slug' => 'c', 'parent_id' => $b->id]);
        $root = Page::create(['title' => 'Root', 'slug' => 'root']);

        $this->actingAs($this->editor())->ajax()->put(route('admin.pages.update', $b), $this->payload(['title' => 'B', 'slug' => 'bee', 'parent_id' => $root->id]))
            ->assertOk();

        $this->assertSame('root/bee', $b->fresh()->path);
        $this->assertSame('root/bee/c', $c->fresh()->path);
    }

    public function test_a_page_cannot_become_its_own_descendant(): void
    {
        $a = Page::create(['title' => 'A', 'slug' => 'a']);
        $b = Page::create(['title' => 'B', 'slug' => 'b', 'parent_id' => $a->id]);

        $this->actingAs($this->editor())->ajax()->put(route('admin.pages.update', $a), $this->payload(['title' => 'A', 'slug' => 'a', 'parent_id' => $b->id]))
            ->assertStatus(422)->assertJsonValidationErrors('parent_id');
    }

    public function test_toggle_trash_restore_and_force_delete(): void
    {
        $page = Page::create(['title' => 'Temp', 'slug' => 'temp', 'is_active' => true]);
        $editor = $this->editor();

        $this->actingAs($editor)->ajax()->patch(route('admin.pages.toggle', $page))->assertOk()->assertJsonPath('data.active', false);
        $this->actingAs($editor)->ajax()->delete(route('admin.pages.destroy', $page))->assertOk();
        $this->assertSoftDeleted($page);
        $this->actingAs($editor)->ajax()->patch(route('admin.pages.restore', $page->id))->assertOk();
        $this->assertNotSoftDeleted($page);

        $page->delete();
        $this->actingAs($editor)->ajax()->delete(route('admin.pages.force-delete', $page->id))->assertForbidden();
        $this->actingAs($this->admin())->ajax()->delete(route('admin.pages.force-delete', $page->id))->assertOk();
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_listing_supports_ajax_partials(): void
    {
        Page::create(['title' => 'Findable Page', 'slug' => 'findable']);

        $this->actingAs($this->editor())->ajax()->get('/admin/pages?q=Findable&partial=1')
            ->assertOk()->assertJsonPath('success', true)
            ->assertJson(fn ($json) => $json->where('data.html', fn ($html) => str_contains($html, 'Findable Page'))->etc());
    }
}
