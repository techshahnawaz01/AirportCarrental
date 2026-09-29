<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_hierarchical_pages_resolve_by_full_path(): void
    {
        $parent = Page::create(['title' => 'Guides', 'slug' => 'guides', 'is_active' => true]);
        Page::create(['title' => 'Parking Guide', 'slug' => 'parking', 'parent_id' => $parent->id, 'is_active' => true, 'content' => '<p>Park here</p>']);

        $this->get('/guides/parking')->assertOk()->assertSee('Parking Guide')->assertSee('Park here');
        // Unknown prefix + unique slug → canonical URL (legacy URL support)
        $this->get('/old/parking')->assertRedirect(url('/guides/parking'));
        $this->get('/guides/missing')->assertNotFound();
    }

    public function test_drafts_are_hidden_from_guests_but_previewable_by_staff(): void
    {
        Page::create(['title' => 'Secret', 'slug' => 'secret', 'is_active' => false]);

        $this->get('/secret')->assertNotFound();
        $this->actingAs($this->editor())->get('/secret')->assertOk()->assertSee('Preview');
    }

    public function test_shortcodes_render_inside_content(): void
    {
        Page::create(['title' => 'Contact', 'slug' => 'contact', 'is_active' => true, 'content' => '<p>[contact_form title="Write to us"]</p>']);

        $this->get('/contact')->assertOk()->assertSee('Write to us')->assertSee(route('enquiries.store'), false);
    }

    public function test_contact_form_validation_uses_json_envelope(): void
    {
        $this->ajax()->post('/enquiries', ['name' => '', 'email' => 'nope'])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Please fix the errors.'])
            ->assertJsonStructure(['errors' => ['name', 'email', 'message']]);
    }

    public function test_contact_form_stores_enquiry(): void
    {
        $this->ajax()->post('/enquiries', [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'message' => 'Where is the cell phone lot?',
        ])->assertCreated()->assertJson(['success' => true]);

        $this->assertDatabaseHas('enquiries', ['email' => 'jane@example.com']);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->ajax()->post('/enquiries', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Buy cheap things now!!', 'website' => 'spam.example',
        ])->assertStatus(422);

        $this->assertSame(0, Enquiry::count());
    }

    public function test_newsletter_subscription_is_idempotent(): void
    {
        $this->ajax()->post('/subscribe', ['email' => 'A@Example.com'])->assertCreated();
        $this->ajax()->post('/subscribe', ['email' => 'a@example.com'])->assertCreated();

        $this->assertSame(1, Subscriber::count());
    }

    public function test_comments_are_held_for_moderation(): void
    {
        $page = Page::create(['title' => 'Post', 'slug' => 'post', 'type' => 'post', 'is_active' => true, 'allow_comments' => true]);

        $this->ajax()->post(route('comments.store', $page), ['name' => 'Sam', 'email' => 's@example.com', 'rating' => 5, 'body' => 'Very helpful guide.'])
            ->assertCreated();

        $this->assertFalse(Comment::first()->is_approved);
        $this->get('/post')->assertDontSee('Very helpful guide.');
    }

    public function test_search_returns_html_and_json(): void
    {
        Page::create(['title' => 'Lounges at MIA', 'slug' => 'lounges', 'is_active' => true]);

        $this->get('/search?q=Lounge')->assertOk()->assertSee('Lounges at MIA');
        $this->ajax()->get('/search?q=Lounge')->assertOk()->assertJsonPath('data.results.0.title', 'Lounges at MIA');
    }

    public function test_branded_404_page(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('Page not found');
    }
}
