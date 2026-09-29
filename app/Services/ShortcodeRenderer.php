<?php

namespace App\Services;

use App\Models\Page;
use App\Shortcodes\Shortcode;
use Throwable;

/**
 * Renders [name attr="value"] and [name]inner[/name] tags in page content
 * using the classes registered in config('cms.shortcodes').
 */
class ShortcodeRenderer
{
    public function render(?string $content, ?Page $page = null): string
    {
        $content = (string) $content;
        $registry = config('cms.shortcodes', []);

        if ($content === '' || ! str_contains($content, '[') || empty($registry)) {
            return $content;
        }

        $names = implode('|', array_map('preg_quote', array_keys($registry)));

        // Editors wrap shortcodes in paragraphs; unwrap them so block output stays valid HTML.
        $content = preg_replace('#<p>\s*(\[/?(?:'.$names.')\b[^\]]*\])\s*</p>#i', '$1', $content);

        $pattern = '#\[('.$names.')\b([^\]]*)\](?:(.*?)\[/\1\])?#s';

        return preg_replace_callback($pattern, function (array $match) use ($registry, $page) {
            $class = $registry[$match[1]] ?? null;

            if (! $class || ! is_subclass_of($class, Shortcode::class)) {
                return $match[0];
            }

            try {
                return app($class)->render($this->parseAttributes($match[2]), $match[3] ?? null, $page);
            } catch (Throwable $e) {
                report($e);

                return '';
            }
        }, $content);
    }

    public function parseAttributes(string $text): array
    {
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = str_replace(['“', '”', '″'], '"', $text);
        preg_match_all('/([\w-]+)\s*=\s*"([^"]*)"|([\w-]+)\s*=\s*\'([^\']*)\'|([\w-]+)\s*=\s*([^\s"\']+)/', $text, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $m) {
            if (! empty($m[1])) {
                $attributes[strtolower($m[1])] = $m[2];
            } elseif (! empty($m[3])) {
                $attributes[strtolower($m[3])] = $m[4];
            } elseif (! empty($m[5])) {
                $attributes[strtolower($m[5])] = $m[6];
            }
        }

        return $attributes;
    }
}
