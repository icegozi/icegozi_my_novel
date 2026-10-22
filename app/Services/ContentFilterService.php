<?php

namespace App\Services;

use App\Models\ContentFilter;

class ContentFilterService
{
    /**
     * Vietnamese accent transliteration mapping.
     */
    protected static array $vietnameseMap = [
        'a' => ['á','à','ả','ã','ạ','ă','ắ','ằ','ẳ','ẵ','ặ','â','ấ','ầ','ẩ','ẫ','ậ'],
        'e' => ['é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ'],
        'i' => ['í','ì','ỉ','ĩ','ị'],
        'o' => ['ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ'],
        'u' => ['ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự'],
        'y' => ['ý','ỳ','ỷ','ỹ','ỵ'],
        'd' => ['đ'],
        'A' => ['Á','À','Ả','Ã','Ạ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ'],
        'E' => ['É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ'],
        'I' => ['Í','Ì','Ỉ','Ĩ','Ị'],
        'O' => ['Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ'],
        'U' => ['Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự'],
        'Y' => ['Ý','Ỳ','Ỷ','Ỹ','Ỵ'],
        'D' => ['Đ'],
    ];

    /**
     * Normalize Vietnamese text by removing diacritics.
     */
    public function removeAccents(string $text): string
    {
        foreach (self::$vietnameseMap as $nonAccented => $accentedList) {
            $text = str_replace($accentedList, $nonAccented, $text);
        }

        return $text;
    }

    /**
     * Check if a pattern is potentially dangerous for ReDoS (catastrophic backtracking).
     */
    public function isUnsafeRegex(string $pattern): bool
    {
        // Detect common ReDoS anti-patterns like (a+)+, (a*)*, (a|b+)+, (.*a){x}
        if (preg_match('/(\([^\)]*[\+\*][^\)]*\)[\+\*])/', $pattern)) {
            return true;
        }

        return false;
    }

    /**
     * Scan text against content filters with ReDoS protection and accent normalization.
     */
    public function scan(string $text): array
    {
        $filters = ContentFilter::all();
        $violations = [];
        $maxSeverity = null;

        $rawLower = mb_strtolower($text, 'UTF-8');
        $unaccentedLower = mb_strtolower($this->removeAccents($text), 'UTF-8');

        // Set low backtrack limit to mitigate ReDoS during regex evaluation
        $oldBacktrack = ini_get('pcre.backtrack_limit');
        @ini_set('pcre.backtrack_limit', '10000');

        try {
            foreach ($filters as $filter) {
                $pattern = trim($filter->pattern);
                $isMatch = false;

                // Check if it's treated as regex or if unsafe
                if ($this->isUnsafeRegex($pattern)) {
                    // Safe fallback: treat as exact literal substring match to prevent ReDoS hanging
                    $isMatch = (stripos($rawLower, $pattern) !== false) ||
                               (stripos($unaccentedLower, $this->removeAccents($pattern)) !== false);
                } else {
                    $patternUnaccented = mb_strtolower($this->removeAccents($pattern), 'UTF-8');
                    $patternRaw = mb_strtolower($pattern, 'UTF-8');

                    // Check literal substring first
                    if (str_contains($rawLower, $patternRaw) || str_contains($unaccentedLower, $patternUnaccented)) {
                        $isMatch = true;
                    } elseif (@preg_match('/' . preg_quote($pattern, '/') . '/iu', $text)) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $violations[] = [
                        'id' => $filter->id,
                        'pattern' => $filter->pattern,
                        'category' => $filter->category,
                        'severity' => $filter->severity,
                    ];

                    if ($filter->severity === 'auto_block') {
                        $maxSeverity = 'auto_block';
                    } elseif ($maxSeverity !== 'auto_block' && $filter->severity === 'flag_for_review') {
                        $maxSeverity = 'flag_for_review';
                    }
                }
            }
        } finally {
            if ($oldBacktrack !== false) {
                @ini_set('pcre.backtrack_limit', $oldBacktrack);
            }
        }

        return [
            'has_violation' => !empty($violations),
            'max_severity' => $maxSeverity,
            'violations' => $violations,
        ];
    }
}
