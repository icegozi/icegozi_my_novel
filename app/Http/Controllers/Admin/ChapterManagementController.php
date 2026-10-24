<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\Novel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChapterManagementController extends Controller
{
    /**
     * Display a list of all chapters belonging to a novel.
     */
    public function index(Request $request, Novel $novel): View
    {
        Gate::authorize('viewAnyAdmin', Chapter::class);

        $chapters = $novel->chapters()
            ->orderBy('chapter_number', 'asc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.chapters.index', compact('novel', 'chapters'));
    }

    /**
     * Show the edit form for a chapter.
     */
    public function edit(Chapter $chapter): View
    {
        Gate::authorize('updateAdmin', $chapter);

        $chapter->load('novel');

        return view('admin.chapters.edit', compact('chapter'));
    }

    /**
     * Update the chapter content, increment revision, and store snapshot in chapter_versions.
     */
    public function update(Request $request, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('updateAdmin', $chapter);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,pending_approval,published,scheduled,rejected,archived',
            'scheduled_at' => 'nullable|date',
            'change_summary' => 'nullable|string|max:255',
        ]);

        // 1. Snapshot previous revision to chapter_versions
        ChapterVersion::create([
            'chapter_id' => $chapter->id,
            'revision' => $chapter->revision,
            'title' => $chapter->title,
            'content' => $chapter->content,
            'word_count' => $chapter->word_count,
            'edited_by' => auth()->id(),
            'change_summary' => $validated['change_summary'] ?? 'Lưu bản chỉnh lý tự động',
        ]);

        // 2. Compute word count
        $plainText = trim(preg_replace('/\s+/', ' ', strip_tags($validated['content'])));
        $wordCount = $plainText !== '' ? count(explode(' ', $plainText)) : 0;

        $oldValues = $chapter->only(['title', 'status', 'revision', 'word_count']);

        // 3. Increment revision and update chapter
        $updatePayload = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => $validated['status'],
            'word_count' => $wordCount,
            'revision' => $chapter->revision + 1,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ];

        if ($validated['status'] === 'published' && !$chapter->published_at) {
            $updatePayload['published_at'] = now();
        }

        $chapter->update($updatePayload);

        // 4. Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_chapter_updated',
            'target_type' => 'Chapter',
            'target_id' => $chapter->id,
            'old_values' => $oldValues,
            'new_values' => $chapter->only(array_keys($oldValues)),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.novels.chapters.index', $chapter->novel_id)
            ->with('success', "Đã cập nhật '{$chapter->title}' (Phiên bản rev #{$chapter->revision}) thành công.");
    }
}
