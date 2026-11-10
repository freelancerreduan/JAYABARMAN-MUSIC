<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('media')) {
    /** URL for a saved path (assets/... or uploads/...) or a full link. */
    function media(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return preg_match('~^(https?:)?//~', $path) ? $path : asset($path);
    }
}

if (! function_exists('clean_html')) {
    /** Keeps the simple formatting the editor makes (bold, links, lists) and drops anything else. */
    function clean_html(?string $html): string
    {
        $html = strip_tags((string) $html, '<p><br><b><strong><i><em><u><a><ul><ol><li><h3><h4>');
        $html = preg_replace('/\s(on\w+|style|class)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $html);

        return trim($html) === '<p><br></p>' ? '' : $html;
    }
}
