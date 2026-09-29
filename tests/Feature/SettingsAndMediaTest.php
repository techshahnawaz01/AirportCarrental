<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsAndMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_colours_are_validated_and_applied(): void
    {
        $admin = $this->admin();
        $colours = ['primary_color' => '#112233', 'secondary_color' => '#000000', 'accent_color' => '#ffcc00', 'button_color' => '#112233', 'text_color' => '#111111', 'background_color' => '#ffffff'];

        $this->actingAs($admin)->ajax()->put('/admin/settings/theme', ['values' => ['primary_color' => 'red'] + $colours])
            ->assertStatus(422)->assertJsonValidationErrors('values.primary_color');

        $this->actingAs($admin)->ajax()->put('/admin/settings/theme', ['values' => $colours])->assertOk();

        $this->assertSame('#112233', settings('theme.primary_color'));
        $this->assertStringContainsString('--brand-primary:#112233', settings()->cssVariables());
    }

    public function test_logo_upload_replace_and_remove(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->ajax()->put('/admin/settings/branding', [
            'values' => ['site_name' => 'Acme Travel'],
            'uploads' => ['logo' => UploadedFile::fake()->image('logo.png', 400, 80)],
        ])->assertOk()->assertJsonPath('success', true);

        $path = settings('branding.logo');
        $this->assertStringStartsWith('media/branding/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('Acme Travel', settings('branding.site_name'));

        $this->actingAs($admin)->ajax()->delete(route('admin.settings.image.destroy', 'branding.logo'))->assertOk();
        $this->assertNull(settings('branding.logo'));
    }

    public function test_favicon_must_be_square(): void
    {
        $this->actingAs($this->admin())->ajax()->put('/admin/settings/branding', [
            'values' => ['site_name' => 'Acme'],
            'uploads' => ['favicon' => UploadedFile::fake()->image('icon.png', 64, 32)],
        ])->assertStatus(422)->assertJsonValidationErrors('uploads.favicon');
    }

    public function test_media_library_rejects_non_images_and_deduplicates(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->ajax()->post('/admin/media', ['files' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')]])
            ->assertStatus(422);

        $image = UploadedFile::fake()->image('photo.jpg', 300, 200);
        $this->actingAs($editor)->ajax()->post('/admin/media', ['files' => [$image]])->assertCreated();
        $this->actingAs($editor)->ajax()->post('/admin/media', ['files' => [$image]])->assertCreated();

        $this->assertSame(1, Media::count());
        $media = Media::first();
        $this->assertSame(300, $media->width);

        $this->actingAs($editor)->ajax()->delete(route('admin.media.destroy', $media))->assertOk();
        Storage::disk('public')->assertMissing($media->path);
    }
}
