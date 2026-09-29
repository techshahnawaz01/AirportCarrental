<?php

namespace Tests\Unit;

use App\Services\ShortcodeRenderer;
use PHPUnit\Framework\TestCase;

class ShortcodeRendererTest extends TestCase
{
    public function test_it_parses_quoted_and_bare_attributes(): void
    {
        $attributes = (new ShortcodeRenderer)->parseAttributes(' title="Hello world" limit=5 style=\'icons\' ');

        $this->assertSame(['title' => 'Hello world', 'limit' => '5', 'style' => 'icons'], $attributes);
    }
}
