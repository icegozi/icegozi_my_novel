<?php

namespace App\Helpers;

class FormulaSanitizerHelper
{
    /**
     * Dangerous formula characters according to OWASP CSV Injection guidelines.
     */
    protected const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Sanitize a single scalar value to neutralize CSV formula injection attacks.
     * Prefixes dangerous starting characters with a single quote (').
     */
    public static function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $str = (string) $value;

        if ($str === '') {
            return '';
        }

        $firstChar = mb_substr($str, 0, 1);

        if (in_array($firstChar, self::DANGEROUS_PREFIXES, true)) {
            return "'" . $str;
        }

        $spaceTrimmed = ltrim($str, ' ');
        if ($spaceTrimmed !== '' && in_array(mb_substr($spaceTrimmed, 0, 1), self::DANGEROUS_PREFIXES, true)) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Sanitize an entire associative or indexed array of row values.
     *
     * @param array<string|int, mixed> $row
     * @return array<string|int, string>
     */
    public static function sanitizeRow(array $row): array
    {
        $sanitized = [];
        foreach ($row as $key => $val) {
            $sanitized[$key] = self::sanitize($val);
        }
        return $sanitized;
    }
}
