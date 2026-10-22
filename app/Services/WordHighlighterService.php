<?php

namespace App\Services;

use App\Models\ContentFilter;

class WordHighlighterService
{
    /**
     * Check if a pattern is potentially dangerous for ReDoS (catastrophic backtracking).
     */
    public function isUnsafeRegex(string $pattern): bool
    {
        if (preg_match('/(\([^\)]*[\+\*][^\)]*\)[\+\*])/', $pattern)) {
            return true;
        }

        return false;
    }

    /**
     * Highlight forbidden/sensitive words within HTML text nodes without corrupting HTML tags/attributes.
     *
     * @param string $html The input HTML or text
     * @param array|null $patterns Custom patterns or null to use database ContentFilter
     * @return string Highlighted HTML string
     */
    public function highlight(string $html, ?array $patterns = null): string
    {
        if (empty($html)) {
            return '';
        }

        if ($patterns === null) {
            $patterns = ContentFilter::pluck('pattern')->filter()->values()->toArray();
        }

        if (empty($patterns)) {
            return $html;
        }

        // Sort patterns by length descending to match compound phrases before substrings
        usort($patterns, function ($a, $b) {
            return mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8');
        });

        // Split HTML by tags to isolate text nodes
        $tokens = preg_split('/(<[^>]+>)/us', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($tokens === false || empty($tokens)) {
            return $html;
        }

        $oldBacktrack = ini_get('pcre.backtrack_limit');
        @ini_set('pcre.backtrack_limit', '10000');

        try {
            foreach ($tokens as $index => $token) {
                // If it's an HTML tag, do not modify
                if (str_starts_with($token, '<') && str_ends_with($token, '>')) {
                    continue;
                }

                // If empty or pure whitespace
                if (trim($token) === '') {
                    continue;
                }

                foreach ($patterns as $pattern) {
                    $pattern = trim($pattern);
                    if ($pattern === '') {
                        continue;
                    }

                    // ReDoS protection check
                    if ($this->isUnsafeRegex($pattern)) {
                        // Safe fallback: match as literal string only
                        $quoted = preg_quote($pattern, '/');
                    } else {
                        $quoted = preg_quote($pattern, '/');
                    }

                    $regex = '/' . $quoted . '/iu';

                    $token = preg_replace(
                        $regex,
                        '<mark class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50" title="Từ khóa nhạy cảm">$0</mark>',
                        $token
                    );
                }

                $tokens[$index] = $token;
            }
        } finally {
            if ($oldBacktrack !== false) {
                @ini_set('pcre.backtrack_limit', $oldBacktrack);
            }
        }

        return implode('', $tokens);
    }
}
