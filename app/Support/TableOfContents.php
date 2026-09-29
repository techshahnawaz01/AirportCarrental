<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Adds anchor ids to <h2>/<h3> headings in rendered content and returns
 * the outline used by the Guide template's table of contents.
 */
class TableOfContents
{
    /**
     * @return array{0: string, 1: array<int, array{id: string, text: string, level: int}>}
     */
    public static function build(string $html): array
    {
        $items = [];
        $used = [];

        $html = preg_replace_callback('#<h([23])([^>]*)>(.*?)</h\1>#is', function (array $m) use (&$items, &$used) {
            $text = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES));
            if ($text === '') {
                return $m[0];
            }

            if (preg_match('/\sid="([^"]+)"/i', $m[2], $existing)) {
                $id = $existing[1];
                $attributes = $m[2];
            } else {
                $base = Str::slug(Str::limit($text, 60, '')) ?: 'section';
                $id = $base;
                for ($i = 2; isset($used[$id]); $i++) {
                    $id = "{$base}-{$i}";
                }
                $attributes = $m[2].' id="'.$id.'"';
            }

            $used[$id] = true;
            $items[] = ['id' => $id, 'text' => $text, 'level' => (int) $m[1]];

            return "<h{$m[1]}{$attributes}>{$m[3]}</h{$m[1]}>";
        }, $html) ?? $html;

        return [$html, $items];
    }
}
