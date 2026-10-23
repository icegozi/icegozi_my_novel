<?php

namespace App\Services;

class ContentSanitizerService
{
    /**
     * Allowed HTML tags for novel chapters.
     */
    private const ALLOWED_TAGS = '<p><br><b><strong><i><em><u><s><h3><h4><blockquote><hr><span><div><ul><ol><li>';

    /**
     * Cleanse HTML content from potential XSS attack vectors.
     *
     * @param string $content
     * @return string
     */
    public function sanitize(string $content): string
    {
        // 1. Remove script, style, and iframe tags along with their inner contents
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleaned);
        $cleaned = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $cleaned);

        // 2. Strip disallowed HTML tags
        $cleaned = strip_tags($cleaned, self::ALLOWED_TAGS);

        // 3. Remove inline javascript/event handlers (e.g. onerror=, onclick=, onload=)
        $cleaned = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned);

        // 4. Remove javascript: protocol in href or src
        $cleaned = preg_replace('/(href|src)\s*=\s*["\']\s*javascript:[^"\']*["\']/i', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Alias for sanitize.
     */
    public function clean(string $content): string
    {
        return $this->sanitize($content);
    }

    /**
     * Accurately count words for Vietnamese and multilingual text.
     *
     * @param string $content
     * @return int
     */
    public function countWords(string $content): int
    {
        $plainText = strip_tags($content);
        $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = trim($plainText);

        if ($plainText === '') {
            return 0;
        }

        // Split by Unicode whitespace
        $words = preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($words) ? count($words) : 0;
    }
}
