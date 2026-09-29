<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/settings')->assertRedirect(route('admin.login'));
        $this->ajax()->put('/admin/settings/branding')->assertUnauthorized()->assertJson(['success' => false]);
    }

    public function test_login_returns_redirect_json_and_rejects_bad_credentials(): void
    {
        $user = $this->admin(['email' => 'boss@example.com', 'password' => 'Secret123']);

        $this->ajax()->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'wrong'])
            ->assertStatus(422)->assertJsonPath('errors.email.0', 'These credentials do not match an active account.');

        $this->ajax()->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'Secret123'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.redirect', route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_users_cannot_sign_in(): void
    {
        $this->admin(['email' => 'off@example.com', 'password' => 'Secret123', 'is_active' => false]);

        $this->ajax()->post('/admin/login', ['email' => 'off@example.com', 'password' => 'Secret123'])->assertStatus(422);
        $this->assertGuest();
    }

    public function test_editors_cannot_manage_site_configuration(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get('/admin')->assertOk();
        $this->actingAs($editor)->get('/admin/pages')->assertOk();
        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/menus')->assertForbidden();
        $this->actingAs($editor)->ajax()->put('/admin/settings/branding', ['values' => ['site_name' => 'Hacked']])->assertForbidden();
    }

    public function test_every_admin_screen_renders_for_admins(): void
    {
        $admin = $this->admin();

        foreach (['/admin', '/admin/pages', '/admin/pages?type=post', '/admin/pages/create?type=hotel', '/admin/media', '/admin/comments',
            '/admin/enquiries', '/admin/subscribers', '/admin/settings/branding', '/admin/settings/theme', '/admin/settings/general',
            '/admin/settings/contact', '/admin/settings/social', '/admin/settings/integrations', '/admin/seo', '/admin/menus',
            '/admin/users', '/admin/users/create', '/admin/profile', '/admin/activity'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admins_cannot_remove_their_own_access(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->ajax()->patch(route('admin.users.toggle', $admin))
            ->assertStatus(422)->assertJson(['success' => false]);
        $this->actingAs($admin)->ajax()->delete(route('admin.users.destroy', $admin))->assertStatus(422);
    }
}
