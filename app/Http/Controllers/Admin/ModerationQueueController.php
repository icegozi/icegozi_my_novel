<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModerationQueueController extends Controller
{
    /**
     * Display the moderation queue with pending novels and chapters.
     */
    public function queue(): View
    {
        $pendingNovels = Novel::where('status', 'pending_approval')
            ->with(['author.authorProfile', 'genres'])
            ->latest()
            ->get();

        $pendingChapters = Chapter::where('status', 'pending_approval')
            ->with(['novel.author.authorProfile'])
            ->latest()
            ->get();

        return view('admin.moderation.queue', compact('pendingNovels', 'pendingChapters'));
    }

    /**
     * Review a specific chapter in the queue.
     */
    public function reviewChapter(Chapter $chapter): View
    {
        $chapter->load(['novel.author.authorProfile']);

        return view('admin.moderation.review_chapter', compact('chapter'));
    }

    /**
     * 1-Click approve chapter.
     */
    public function approveChapter(Chapter $chapter): RedirectResponse
    {
        $oldStatus = $chapter->status;

        $chapter->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $chapter->novel()->increment('total_chapters');

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'chapter_approved',
            'target_type' => 'Chapter',
            'target_id' => $chapter->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'published', 'published_at' => $chapter->published_at],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.moderation.queue')
            ->with('success', 'Chương truyện đã được phê duyệt và xuất bản thành công.');
    }

    /**
     * Reject chapter with a designated reason.
     */
    public function rejectChapter(Request $request, Chapter $chapter): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $chapter->status;

        $chapter->update([
            'status' => 'rejected',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'chapter_rejected',
            'target_type' => 'Chapter',
            'target_id' => $chapter->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'rejected',
                'reason' => $request->input('rejection_reason'),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.moderation.queue')
            ->with('warning', 'Chương truyện đã bị từ chối phê duyệt.');
    }

    /**
     * 1-Click approve novel.
     */
    public function approveNovel(Novel $novel): RedirectResponse
    {
        $oldStatus = $novel->status;

        $novel->update([
            'status' => 'published',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'novel_approved',
            'target_type' => 'Novel',
            'target_id' => $novel->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'published',
                'approved_at' => $novel->approved_at,
                'approved_by' => auth()->id(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.moderation.queue')
            ->with('success', 'Tác phẩm đã được phê duyệt và xuất bản thành công.');
    }

    /**
     * Reject novel with a reason.
     */
    public function rejectNovel(Request $request, Novel $novel): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $novel->status;

        $novel->update([
            'status' => 'rejected',
            'rejection_notes' => $request->input('rejection_reason'),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'novel_rejected',
            'target_type' => 'Novel',
            'target_id' => $novel->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'rejected',
                'reason' => $request->input('rejection_reason'),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.moderation.queue')
            ->with('warning', 'Tác phẩm đã bị từ chối phê duyệt.');
    }
}
