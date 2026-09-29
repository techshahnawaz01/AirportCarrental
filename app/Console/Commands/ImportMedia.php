<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Throwable;

class ImportMedia extends Command
{
    protected $signature = 'cms:import-media {directory : Folder containing images, e.g. legacy/Pic}';

    protected $description = 'Import every image in a folder into the media library (duplicates are skipped)';

    public function handle(MediaService $media): int
    {
        $directory = realpath($this->argument('directory'));
        if (! $directory || ! is_dir($directory)) {
            $this->components->error('Directory not found.');

            return self::FAILURE;
        }

        $files = Finder::create()->files()->in($directory)->name('/\.(jpe?g|png|webp|gif)$/i');
        $bar = $this->output->createProgressBar(iterator_count($files));
        $imported = 0;

        foreach ($files as $file) {
            try {
                $before = Media::count();
                $media->importFromPath($file->getRealPath(), $file->getFilename());
                $imported += Media::count() > $before ? 1 : 0;
            } catch (Throwable $e) {
                $this->newLine();
                $this->components->warn($file->getRelativePathname().': '.$e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->components->info("Imported {$imported} new images.");

        return self::SUCCESS;
    }
}
