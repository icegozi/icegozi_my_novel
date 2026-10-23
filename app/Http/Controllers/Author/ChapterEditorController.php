<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Novel;
use App\Services\ContentSanitizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChapterEditorController extends Controller
{
    /**
     * Show editor for an existing chapter.
     */
    public function edit(Request $request, Novel $truyen, Chapter $chuong): View
    {
        abort_if(
            $truyen->author_id !== $request->user()->id || $chuong->novel_id !== $truyen->id,
            403,
            'Bạn không có quyền chỉnh sửa chương này.'
        );

        $novel = $truyen;
        $chapter = $chuong;

        return view('author.chapters.editor', compact('novel', 'chapter'));
    }

    /**
     * Create a new chapter draft and redirect to its editor.
     */
    public function create(Request $request, Novel $truyen): View
    {
        abort_if($truyen->author_id !== $request->user()->id, 403);

        $nextNumber = floor(($truyen->chapters()->max('chapter_number') ?? 0) + 1);

        $chapter = Chapter::create([
            'novel_id' => $truyen->id,
            'chapter_number' => $nextNumber,
            'title' => "Chương {$nextNumber}: Chưa đặt tên",
            'slug' => "chuong-{$nextNumber}-" . substr(uniqid(), -5),
            'content' => '<p></p>',
            'word_count' => 0,
            'revision' => 1,
            'status' => 'draft',
        ]);

        $novel = $truyen;

        return view('author.chapters.editor', compact('novel', 'chapter'));
    }

    /**
     * API Autosave endpoint with revision conflict detection and XSS sanitization.
     */
    public function autosave(Request $request, ContentSanitizerService $sanitizer): JsonResponse
    {
        $validated = $request->validate([
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'revision' => ['required', 'integer', 'min:1'],
            'client_uuid' => ['nullable', 'string', 'max:64'],
        ]);

        $chapter = Chapter::with('novel')->findOrFail($validated['chapter_id']);

        abort_if(
            $chapter->novel->author_id !== $request->user()->id,
            403,
            'Bạn không có quyền thao tác trên chương này.'
        );

        // Optimistic locking via revision check
        if ($chapter->revision > $validated['revision']) {
            return response()->json([
                'status' => 'conflict',
                'message' => 'Bản thảo đã được cập nhật bởi một phiên làm việc mới hơn trên server.',
                'current_revision' => $chapter->revision,
                'server_content' => $chapter->content,
                'server_title' => $chapter->title,
            ], 409, [], JSON_UNESCAPED_UNICODE);
        }

        // Cleanse XSS attack vectors
        $cleanContent = $sanitizer->sanitize($validated['content']);
        $wordCount = $sanitizer->countWords($cleanContent);

        $chapter->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . substr(uniqid(), -5),
            'content' => $cleanContent,
            'word_count' => $wordCount,
            'revision' => $validated['revision'],
            'client_uuid' => $validated['client_uuid'] ?? $chapter->client_uuid,
        ]);

        return response()->json([
            'status' => 'saved',
            'current_revision' => $chapter->revision,
            'word_count' => $chapter->word_count,
            'saved_at' => now()->toIso8601String(),
        ]);
    }
}
