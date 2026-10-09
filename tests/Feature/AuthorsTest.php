<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthorsTest extends TestCase
{
    use RefreshDatabase;

    private function author(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_AUTHOR, 'is_active' => true]);
    }

    private function blogPost(User $author, array $attributes = []): Page
    {
        return Page::create($attributes + [
            'title' => 'Tips for flying out of CLT', 'slug' => 'tips-'.uniqid(), 'type' => 'post', 'template' => 'post',
            'is_active' => true, 'published_at' => now()->subDay(), 'author_id' => $author->id, 'content' => '<p>Arrive early.</p>',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + ['title' => 'My new post', 'slug' => 'my-new-post', 'type' => 'post', 'template' => 'post', 'is_active' => '1'];
    }

    public function test_users_get_a_unique_slug_from_their_name(): void
    {
        $first = $this->author(['name' => 'Priya Sharma']);
        $second = $this->author(['name' => 'Priya Sharma']);

        $this->assertSame('priya-sharma', $first->slug);
        $this->assertSame('priya-sharma-2', $second->slug);
    }

    public function test_author_page_lists_the_authors_published_posts(): void
    {
        $author = $this->author(['name' => 'Priya Sharma', 'job_title' => 'Travel writer', 'bio' => 'Covers Charlotte travel.', 'social' => ['twitter' => 'https://x.com/priya']]);
        $this->blogPost($author, ['title' => 'Parking at CLT explained']);
        $this->blogPost($author, ['title' => 'Unpublished draft', 'is_active' => false]);

        $this->get('/author/priya-sharma')->assertOk()
            ->assertSee('Priya Sharma')->assertSee('Travel writer')->assertSee('Covers Charlotte travel.')
            ->assertSee('Parking at CLT explained')->assertDontSee('Unpublished draft')
            ->assertSee('https://x.com/priya', false)
            ->assertSee('"@type":"ProfilePage"', false);
    }

    public function test_users_without_published_posts_have_no_public_page(): void
    {
        $this->admin(['name' => 'Site Admin']);

        $this->get('/author/site-admin')->assertNotFound();
        $this->get('/author/nobody')->assertNotFound();
    }

    public function test_posts_show_their_author_with_a_link_and_person_schema(): void
    {
        $author = $this->author(['name' => 'Priya Sharma', 'bio' => 'Covers Charlotte travel.']);
        $post = $this->blogPost($author);

        $this->get($post->url())->assertOk()
            ->assertSee('href="'.url('/author/priya-sharma').'"', false)
            ->assertSee('About the author')
            ->assertSee('"@type":"Person"', false);
    }

    public function test_sitemap_lists_author_pages(): void
    {
        $this->blogPost($this->author(['name' => 'Priya Sharma']));

        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>'.url('/author/priya-sharma').'</loc>', false);
    }

    public function test_authors_only_manage_their_own_posts(): void
    {
        $author = $this->author();
        $mine = $this->blogPost($author, ['title' => 'Mine']);
        $theirs = $this->blogPost($this->author(), ['title' => 'Someone else']);
        $page = Page::create(['title' => 'About', 'slug' => 'about', 'type' => 'page', 'template' => 'default', 'is_active' => true]);

        $this->actingAs($author);
        $this->get('/admin')->assertRedirect(route('admin.pages.index', ['type' => 'post']));
        $this->get('/admin/pages?type=page')->assertOk()->assertSee('Mine')->assertDontSee('Someone else')->assertDontSee('About');
        $this->get("/admin/pages/{$mine->id}/edit")->assertOk();
        $this->get("/admin/pages/{$theirs->id}/edit")->assertForbidden();
        $this->get("/admin/pages/{$page->id}/edit")->assertForbidden();
        $this->ajax()->delete("/admin/pages/{$theirs->id}")->assertForbidden();
        $this->get('/admin/enquiries')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();

        // New posts are always their own, whatever the form sends.
        $this->ajax()->post('/admin/pages', $this->payload(['author_id' => $theirs->author_id]))->assertCreated();
        $this->assertSame($author->id, Page::where('slug', 'my-new-post')->value('author_id'));
        $this->ajax()->post('/admin/pages', $this->payload(['slug' => 'a-page', 'type' => 'page', 'template' => 'default']))->assertForbidden();
    }

    public function test_editors_can_choose_a_posts_author(): void
    {
        $writer = $this->author();

        $this->actingAs($this->editor())->ajax()->post('/admin/pages', $this->payload(['author_id' => $writer->id]))->assertCreated();

        $this->assertSame($writer->id, Page::where('slug', 'my-new-post')->value('author_id'));
    }

    public function test_users_edit_their_author_profile(): void
    {
        $user = $this->author(['name' => 'Priya Sharma']);

        $this->actingAs($user)->ajax()->put('/admin/profile', [
            'name' => 'Priya Sharma', 'email' => $user->email, 'slug' => 'Priya S', 'job_title' => 'Travel writer',
            'bio' => 'Covers Charlotte travel.', 'social' => ['linkedin' => 'https://linkedin.com/in/priya', 'facebook' => ''],
        ])->assertOk();

        $user->refresh();
        $this->assertSame('priya-s', $user->slug);
        $this->assertSame('Travel writer', $user->job_title);
        $this->assertSame(['linkedin' => 'https://linkedin.com/in/priya'], $user->socialLinks());

        $this->ajax()->put('/admin/profile', ['name' => 'Priya', 'email' => $user->email, 'social' => ['website' => 'not a url']])
            ->assertStatus(422)->assertJsonValidationErrors('social.website');
    }

    public function test_administrators_can_add_an_author_with_a_photo(): void
    {
        $this->actingAs($this->admin())->ajax()->post('/admin/users', [
            'name' => 'New Writer', 'email' => 'writer@example.com', 'role' => User::ROLE_AUTHOR, 'is_active' => '1',
            'password' => 'secret123', 'password_confirmation' => 'secret123', 'job_title' => 'Guest author',
            'avatar_upload' => UploadedFile::fake()->image('me.jpg', 800, 800),
        ])->assertCreated();

        $user = User::where('email', 'writer@example.com')->first();
        $this->assertSame(User::ROLE_AUTHOR, $user->role);
        $this->assertSame('new-writer', $user->slug);
        $this->assertNotNull($user->avatar);
        $this->assertSame(480, $user->avatar->width, 'Author photos are kept small.');
    }
}
