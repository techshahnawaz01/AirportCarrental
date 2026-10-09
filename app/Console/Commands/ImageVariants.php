<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Makes the phone-sized copies used in srcset for images uploaded before copies existed.
 * New uploads get theirs automatically. Safe to run again: existing copies are skipped.
 */
class ImageVariants extends Command
{
    protected $signature = 'cms:image-variants {--force : Recreate copies that already exist}';

    protected $description = 'Create the smaller image copies served to phones for existing uploads';

    public function handle(MediaService $media): int
    {
        $made = 0;

        Media::where('mime_type', 'like', 'image/%')
            ->where('path', 'not like', 'media/branding/%')
            ->where('width', '>', (int) config('cms.uploads.variant_width', 960))
            ->lazyById()
            ->each(function (Media $item) use ($media, &$made) {
                if (! $this->option('force') && $item->srcset() !== null) {
                    return;
                }
                Storage::disk($item->disk)->delete($item->variantPath());
                if ($media->createVariant($item)) {
                    $made++;
                    $this->line("  {$item->variantPath()}");
                }
            });

        $this->info("Created {$made} image ".str('copy')->plural($made).'.');

        return self::SUCCESS;
    }
}
