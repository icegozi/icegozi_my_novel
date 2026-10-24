<?php

namespace App\Services;

class ContentSanitizerService
{
    /**
     * Allowed HTML tags for novel chapters.
     */
    private const ALLOWED_TAGS = '<p><br><b><strong><i><em><u><s><h3><h4><blockquote><hr><span><div><ul><ol><li><img><figure><figcaption>';

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

    /**
     * Clean and strip Microsoft Word / Google Docs proprietary markup,
     * removing MSO conditionals, inline styles, and namespace tags while preserving semantic structure.
     */
    public function sanitizeWordPaste(string $content): string
    {
        // 1. Remove XML/HTML comments including MSO conditionals (<!--[if ...]>...<![endif]-->)
        $cleaned = preg_replace('/<!--.*?-->/s', '', $content);

        // 2. Remove XML namespace tags such as <o:p>, </o:p>, <w:WordDocument>, etc.
        $cleaned = preg_replace('/<\/?(o|w|m|v):[^>]*>/i', '', $cleaned);

        // 3. Remove all inline style attributes to preserve clean reading theme styling
        $cleaned = preg_replace('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $cleaned);

        // 4. Remove Mso* classes
        $cleaned = preg_replace('/\sclass\s*=\s*("[^"]*Mso[^"]*"|\'[^\']*Mso[^\']*\')/i', '', $cleaned);

        // 5. Convert <b> to <strong>, <i> to <em>
        $cleaned = preg_replace('/<b\b[^>]*>(.*?)<\/b>/i', '<strong>$1</strong>', $cleaned);
        $cleaned = preg_replace('/<i\b[^>]*>(.*?)<\/i>/i', '<em>$1</em>', $cleaned);

        // 6. Cleanse with base sanitizer to eliminate script, iframe, and dangerous event handlers
        return $this->sanitize($cleaned);
    }
}
