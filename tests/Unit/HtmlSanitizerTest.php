<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_it_strips_executable_markup_but_keeps_formatting(): void
    {
        $html = (new HtmlSanitizer)->clean(
            '<h2>Title</h2><p onclick="x()">Text <strong>bold</strong><script>alert(1)</script></p>'
            .'<a href="javascript:alert(1)">bad</a><a href="/ok" target="_blank">ok</a>'
            .'<iframe src="https://evil.example/"></iframe><iframe src="https://www.google.com/maps/embed?pb=1"></iframe>'
            .'<table><tr><td>cell</td></tr></table>'
        );

        $this->assertStringContainsString('<h2>Title</h2>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<td>cell</td>', $html);
        $this->assertStringContainsString('rel="noopener"', $html);
        $this->assertStringContainsString('www.google.com/maps', $html);
        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('evil.example', $html);
    }
}
