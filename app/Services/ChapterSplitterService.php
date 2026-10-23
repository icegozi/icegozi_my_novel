<?php

namespace App\Services;

class ChapterSplitterService
{
    /**
     * Split a long text containing multiple chapters into structured chapter drafts.
     *
     * @param string $rawText
     * @return array<int, array{chapter_number: float, title: string, content: string, word_count: int}>
     */
    public function split(string $rawText): array
    {
        $rawText = trim($rawText);
        if ($rawText === '') {
            return [];
        }

        // Pattern matching Chapter headers at line starts:
        // Examples: "Chương 1: Khởi đầu", "Hồi 1 - Dị giới", "Chapter 1. Test", "Chương 1"
        $pattern = '/(?:^|\r?\n)(?<header>(?:Chương|Hồi|Chapter)\s+(?<number>[0-9]+(?:\.[0-9]+)?)(?:[:\s\-\.][ \t]*(?<subtitle>[^\r\n]*))?)/iu';

        if (!preg_match_all($pattern, $rawText, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            // No recognizable chapter header, treat entire text as single chapter
            return [[
                'chapter_number' => 1.0,
                'title' => 'Chương 1',
                'content' => $rawText,
                'word_count' => $this->countWords($rawText),
            ]];
        }

        $chapters = [];
        $totalMatches = count($matches);

        for ($i = 0; $i < $totalMatches; $i++) {
            $current = $matches[$i];
            $headerText = trim($current['header'][0]);
            $chapterNumber = (float) $current['number'][0];

            // Calculate start and end offset of chapter content
            $headerStart = $current['header'][1];
            $contentStart = $headerStart + strlen($current['header'][0]);

            if ($i + 1 < $totalMatches) {
                $nextHeaderStart = $matches[$i + 1]['header'][1];
                $contentLength = $nextHeaderStart - $contentStart;
                $content = substr($rawText, $contentStart, $contentLength);
            } else {
                $content = substr($rawText, $contentStart);
            }

            $content = trim($content);

            $chapters[] = [
                'chapter_number' => $chapterNumber,
                'title' => $headerText,
                'content' => $content,
                'word_count' => $this->countWords($content),
            ];
        }

        return $chapters;
    }

    /**
     * Count words in UTF-8 text.
     */
    protected function countWords(string $text): int
    {
        $clean = strip_tags($text);
        preg_match_all('/[\p{L}\p{N}]+/u', $clean, $words);
        return count($words[0] ?? []);
    }
}
