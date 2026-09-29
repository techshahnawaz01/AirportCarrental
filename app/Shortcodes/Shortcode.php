<?php

namespace App\Shortcodes;

use App\Models\Page;

/**
 * A content block editors can embed in page content as [name attr="value"]
 * or [name]inner content[/name]. Register implementations in config/cms.php.
 */
interface Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string;
}
