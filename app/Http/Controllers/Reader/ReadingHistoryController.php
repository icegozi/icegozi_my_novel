<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterReadingProgress;
use App\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingHistoryController extends Controller
{
    /**
     * Sync reading progress for a specific chapter and novel.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chapter_id' => ['required', 'exists:chapters,id'],
            'paragraph_index' => ['required', 'integer', 'min:0'],
            'scroll_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $userId = auth()->id();
        $chapter = Chapter::findOrFail($validated['chapter_id']);
        $scrollPercentage = $validated['scroll_percentage'] ?? 0.00;

        if ($userId) {
            // 1. Independent progress per chapter
            ChapterReadingProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'chapter_id' => $chapter->id,
                ],
                [
                    'last_read_paragraph' => $validated['paragraph_index'],
                    'scroll_percentage' => $scrollPercentage,
                ]
            );

            // 2. Novel-level last read chapter tracker
            ReadingHistory::updateOrCreate(
                [
                    'user_id' => $userId,
                    'novel_id' => $chapter->novel_id,
                ],
                [
                    'last_chapter_id' => $chapter->id,
                ]
            );
        }

        return response()->json([
            'status' => 'saved',
            'paragraph_index' => $validated['paragraph_index'],
            'scroll_percentage' => $scrollPercentage,
        ]);
    }

    /**
     * Retrieve reading progress for a specific chapter.
     */
    public function show(Chapter $chapter): JsonResponse
    {
        $userId = auth()->id();

        if (! $userId) {
            return response()->json([
                'last_read_paragraph' => 0,
                'scroll_percentage' => 0.00,
            ]);
        }

        $progress = ChapterReadingProgress::where('user_id', $userId)
            ->where('chapter_id', $chapter->id)
            ->first();

        return response()->json([
            'last_read_paragraph' => $progress ? $progress->last_read_paragraph : 0,
            'scroll_percentage' => $progress ? (float) $progress->scroll_percentage : 0.00,
        ]);
    }
}
