<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\User;
use InvalidArgumentException;

class ChapterVersionService
{
    /**
     * Compute a line-by-line diff between two strings using Longest Common Subsequence (LCS).
     *
     * @return array<int, array{type: 'unchanged'|'added'|'deleted', text: string}>
     */
    public function diff(string $oldText, string $newText): array
    {
        $oldLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $oldText));
        $newLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $newText));

        $m = count($oldLines);
        $n = count($newLines);

        // Build LCS table
        $lcs = [];
        for ($i = 0; $i <= $m; $i++) {
            $lcs[$i] = array_fill(0, $n + 1, 0);
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($oldLines[$i - 1] === $newLines[$j - 1]) {
                    $lcs[$i][$j] = $lcs[$i - 1][$j - 1] + 1;
                } else {
                    $lcs[$i][$j] = max($lcs[$i - 1][$j], $lcs[$i][$j - 1]);
                }
            }
        }

        // Backtrack to assemble diff entries
        $diff = [];
        $i = $m;
        $j = $n;

        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $oldLines[$i - 1] === $newLines[$j - 1]) {
                $diff[] = ['type' => 'unchanged', 'text' => $oldLines[$i - 1]];
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $lcs[$i][$j - 1] >= $lcs[$i - 1][$j])) {
                $diff[] = ['type' => 'added', 'text' => $newLines[$j - 1]];
                $j--;
            } elseif ($i > 0 && ($j === 0 || $lcs[$i][$j - 1] < $lcs[$i - 1][$j])) {
                $diff[] = ['type' => 'deleted', 'text' => $oldLines[$i - 1]];
                $i--;
            }
        }

        return array_reverse($diff);
    }

    /**
     * Revert chapter content and title to a designated prior version snapshot.
     * Safely archives the pre-restoration state as a new snapshot first.
     */
    public function restore(Chapter $chapter, ChapterVersion $version, User $user): Chapter
    {
        if ($version->chapter_id !== $chapter->id) {
            throw new InvalidArgumentException("Phiên bản #{$version->id} không thuộc về chương #{$chapter->id}.");
        }

        // 1. Snapshot current state as backup
        ChapterVersion::create([
            'chapter_id' => $chapter->id,
            'revision' => $chapter->revision,
            'title' => $chapter->title,
            'content' => $chapter->content,
            'word_count' => $chapter->word_count,
            'edited_by' => $user->id,
            'change_summary' => "Tự động sao lưu trước khi khôi phục rev #{$version->revision}",
        ]);

        $oldValues = [
            'revision' => $chapter->revision,
            'title' => $chapter->title,
            'word_count' => $chapter->word_count,
        ];

        // 2. Revert chapter to historical version
        $newRevision = $chapter->revision + 1;

        $chapter->update([
            'title' => $version->title,
            'content' => $version->content,
            'word_count' => $version->word_count,
            'revision' => $newRevision,
        ]);

        // 3. Record Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'admin_chapter_restored',
            'target_type' => 'Chapter',
            'target_id' => $chapter->id,
            'old_values' => $oldValues,
            'new_values' => [
                'restored_from_version_id' => $version->id,
                'restored_from_revision' => $version->revision,
                'new_revision' => $newRevision,
                'title' => $chapter->title,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $chapter;
    }
}
