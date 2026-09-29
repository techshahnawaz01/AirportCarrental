<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Removes executable markup from editor HTML while keeping formatting,
 * tables, images and embeds from trusted providers.
 */
class HtmlSanitizer
{
    private const BLOCKED_TAGS = ['script', 'style', 'object', 'embed', 'applet', 'form', 'input', 'button', 'textarea', 'select', 'meta', 'link', 'base'];

    private const IFRAME_HOSTS = ['www.google.com', 'maps.google.com', 'www.youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'];

    public function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);

        foreach (self::BLOCKED_TAGS as $tag) {
            foreach (iterator_to_array($xpath->query('//'.$tag)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        foreach (iterator_to_array($xpath->query('//iframe')) as $iframe) {
            $host = parse_url((string) $iframe->getAttribute('src'), PHP_URL_HOST);
            if (! in_array($host, self::IFRAME_HOSTS, true)) {
                $iframe->parentNode?->removeChild($iframe);
            }
        }

        foreach (iterator_to_array($xpath->query('//*')) as $element) {
            /** @var DOMElement $element */
            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = strtolower(preg_replace('/\s+/', '', $attribute->value));

                if (str_starts_with($name, 'on')
                    || (in_array($name, ['href', 'src', 'action', 'formaction', 'xlink:href'], true) && preg_match('/^(javascript|vbscript|data:text)/', $value))
                    || ($name === 'style' && preg_match('/expression\(|javascript:|url\(\s*["\']?\s*javascript/', $value))) {
                    $element->removeAttribute($attribute->name);
                }
            }

            if ($element->nodeName === 'a' && $element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener');
            }
        }

        $root = $document->getElementById('__root');
        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }
}
