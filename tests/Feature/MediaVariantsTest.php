<?php

namespace Tests\Feature;

use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaVariantsTest extends TestCase
{
    use RefreshDatabase;

    private function image(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'img').'.jpg';
        $gd = imagecreatetruecolor($width, $height);
        imagefill($gd, 0, 0, imagecolorallocate($gd, 30, 120, 190));
        imagejpeg($gd, $path, 80);
        imagedestroy($gd);

        return $path;
    }

    public function test_large_uploads_get_a_phone_sized_copy_for_srcset(): void
    {
        Storage::fake('public');
        $source = $this->image(1600, 900);
        $before = sha1_file($source);

        $media = app(MediaService::class)->importFromPath($source, 'hero.jpg');

        Storage::disk('public')->assertExists($media->variantPath());
        [$width] = getimagesize(Storage::disk('public')->path($media->variantPath()));
        $this->assertSame(960, $width);
        $this->assertStringContainsString('-960w.jpg 960w', $media->srcset());
        $this->assertStringContainsString('sizes="100vw"', (string) $media->responsive('100vw'));
        // The source file is never modified.
        $this->assertSame($before, sha1_file($source));

        app(MediaService::class)->delete($media);
        Storage::disk('public')->assertMissing([$media->path, $media->variantPath()]);
    }

    public function test_small_images_and_branding_images_get_no_copy(): void
    {
        Storage::fake('public');

        $small = app(MediaService::class)->importFromPath($this->image(800, 600), 'small.jpg');
        $logo = app(MediaService::class)->importFromPath($this->image(1200, 400), 'logo.jpg', 'branding');

        $this->assertNull($small->srcset());
        $this->assertSame('', (string) $small->responsive('100vw'));
        $this->assertSame(480, $logo->width, 'Branding images are capped at 480px.');
        Storage::disk('public')->assertMissing($logo->variantPath());
    }
}
