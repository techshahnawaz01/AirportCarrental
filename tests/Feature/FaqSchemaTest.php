<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function schemaFor(string $url): array
    {
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->assertOk()->getContent(), $m);

        return json_decode($m[1] ?? '[]', true) ?: [];
    }

    private function faqPages(array $schema): array
    {
        return array_values(array_filter($schema['@graph'] ?? [], fn ($node) => ($node['@type'] ?? null) === 'FAQPage'));
    }

    public function test_every_template_with_faqs_outputs_matching_faq_schema(): void
    {
        foreach (array_keys(config('cms.templates')) as $template) {
            $page = Page::create(['title' => "Page {$template}", 'slug' => "p-{$template}", 'template' => $template, 'is_active' => true]);
            $page->faqs()->create(['question' => "Is {$template} open?", 'answer' => '<p>Yes, <strong>24/7</strong>.</p>']);

            $faqs = $this->faqPages($this->schemaFor("/p-{$template}"));

            $this->assertCount(1, $faqs, "Template {$template} should output one FAQPage");
            $this->assertSame("Is {$template} open?", $faqs[0]['mainEntity'][0]['name']);
            $this->assertSame('Yes, 24/7.', $faqs[0]['mainEntity'][0]['acceptedAnswer']['text']);
        }
    }

    public function test_faqs_embedded_from_another_page_are_included_once(): void
    {
        $source = Page::create(['title' => 'Parking', 'slug' => 'parking', 'is_active' => true]);
        $source->faqs()->create(['question' => 'Where is the cell phone lot?', 'answer' => 'Off Lejeune Road.']);

        $page = Page::create(['title' => 'Tips', 'slug' => 'tips', 'template' => 'full-width', 'is_active' => true, 'content' => '<p>[faqs page="parking"]</p>']);
        $page->faqs()->create(['question' => 'Is MIA open 24 hours?', 'answer' => 'Yes.']);
        $page->faqs()->create(['question' => 'Where is the cell phone lot?', 'answer' => 'Duplicate question.']);

        $faqs = $this->faqPages($this->schemaFor('/tips'));
        $questions = array_column($faqs[0]['mainEntity'], 'name');

        $this->assertCount(1, $faqs);
        $this->assertEqualsCanonicalizing(['Where is the cell phone lot?', 'Is MIA open 24 hours?'], $questions);
    }

    public function test_pages_without_faqs_have_no_faq_schema(): void
    {
        Page::create(['title' => 'Plain', 'slug' => 'plain', 'is_active' => true]);

        $schema = $this->schemaFor('/plain');

        $this->assertSame([], $this->faqPages($schema));
        $this->assertContains('BreadcrumbList', array_column($schema['@graph'], '@type'));
    }
}
