<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Collects every FAQ rendered during the current request (page FAQs,
 * [faqs] shortcodes, any template) so the FAQPage schema always matches
 * exactly what visitors see.
 */
class FaqRegistry
{
    /** @var array<string, array{question: string, answer: string}> */
    private array $items = [];

    public function add(iterable $faqs): void
    {
        foreach ($faqs as $faq) {
            $question = trim((string) data_get($faq, 'question'));
            $answer = trim((string) data_get($faq, 'answer'));

            // One entry per question: Google expects each question once per page.
            if ($question !== '' && $answer !== '') {
                $this->items[mb_strtolower($question)] ??= ['question' => $question, 'answer' => $answer];
            }
        }
    }

    public function all(): Collection
    {
        return collect(array_values($this->items));
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
