<?php

namespace App\Support;

/**
 * Puts every table from the editor inside a scroll box (.table-scroll), so wide tables
 * scroll sideways on phones instead of squeezing their text or widening the page.
 */
class ContentTables
{
    public static function wrap(?string $html): string
    {
        $html = (string) $html;

        if (! str_contains($html, '<table')) {
            return $html;
        }

        $html = preg_replace('#<table\b#i', '<div class="table-scroll"><table', $html);

        return preg_replace('#</table>#i', '</table></div>', $html);
    }
}
