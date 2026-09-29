<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Persists a page together with its uploads, FAQs and template data.
 */
class PageService
{
    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly MediaService $media,
        private readonly ActivityLogger $activity,
    ) {}

    public function save(Page $page, array $data, array $files = []): Page
    {
        return DB::transaction(function () use ($page, $data, $files) {
            $isNew = ! $page->exists;

            foreach (['featured_image' => 'featured_image_id', 'og_image' => 'og_image_id'] as $upload => $column) {
                if (($files[$upload.'_upload'] ?? null) instanceof UploadedFile) {
                    $data[$column] = $this->media->upload($files[$upload.'_upload'], null, auth()->id())->id;
                }
            }

            $data['content'] = $this->sanitizer->clean($data['content'] ?? null);
            $data['data'] = $this->templateData($data['template'], Arr::get($data, 'data', []), $page->data ?? []);
            $data['author_id'] ??= $page->author_id ?? auth()->id();

            if (($data['is_active'] ?? false) && empty($data['published_at']) && ! $page->published_at) {
                $data['published_at'] = now();
            }

            $page->fill(Arr::except($data, ['faqs']))->save();
            $this->syncFaqs($page, $data['faqs'] ?? []);

            $this->activity->log($isNew ? 'created' : 'updated', ($isNew ? 'Created ' : 'Updated ').strtolower($page->typeLabel()).' "'.$page->title.'"', $page);

            return $page;
        });
    }

    private function syncFaqs(Page $page, array $faqs): void
    {
        $page->faqs()->delete();

        collect($faqs)
            ->filter(fn ($faq) => filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->values()
            ->each(fn ($faq, $index) => $page->faqs()->create([
                'question' => trim($faq['question']),
                'answer' => $this->formatAnswer($faq['answer']),
                'sort_order' => $index,
            ]));
    }

    /**
     * Answers may be plain text (textarea) or HTML (imported content).
     */
    public function formatAnswer(string $answer): string
    {
        $answer = trim($answer);

        return $answer !== strip_tags($answer)
            ? $this->sanitizer->clean($answer)
            : nl2br(e($answer), false);
    }

    /**
     * Only keep structured data relevant to the chosen template.
     */
    private function templateData(string $template, array $input, array $existing): ?array
    {
        $data = Arr::except($existing, ['address', 'location_title', 'location_description', 'map_embed_url', 'gallery', 'rooms', 'notice']);

        if ($template === 'hotel') {
            $data = array_merge($data, [
                'address' => $input['address'] ?? null,
                'location_title' => $input['location_title'] ?? null,
                'location_description' => $this->sanitizer->clean($input['location_description'] ?? null),
                'map_embed_url' => $input['map_embed_url'] ?? null,
                'notice' => $input['notice'] ?? null,
                'gallery' => array_values(array_unique(array_map('intval', array_filter((array) ($input['gallery'] ?? []))))),
                'rooms' => collect($input['rooms'] ?? [])
                    ->filter(fn ($room) => filled($room['title'] ?? null))
                    ->map(fn ($room) => [
                        'title' => trim($room['title']),
                        'description' => trim((string) ($room['description'] ?? '')),
                        'image_id' => ! empty($room['image_id']) ? (int) $room['image_id'] : null,
                        'amenities' => collect(preg_split('/\r\n|\r|\n/', (string) ($room['amenities'] ?? '')))
                            ->map(fn ($line) => trim($line))->filter()->values()->all(),
                    ])->values()->all(),
            ]);
        }

        $data = array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== []);

        return $data ?: null;
    }
}
