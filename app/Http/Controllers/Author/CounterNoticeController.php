<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterNoticeController extends Controller
{
    /**
     * Show the Counter-Notice submission form for an author.
     */
    public function create(Report $report): View
    {
        $this->authorizeAuthor($report);

        $novel = $this->resolveNovel($report);

        return view('author.counter_notice_form', compact('report', 'novel'));
    }

    /**
     * Process and store author's Counter-Notice under NĐ 17/2023/NĐ-CP.
     */
    public function store(Request $request, Report $report): RedirectResponse
    {
        $this->authorizeAuthor($report);

        $validated = $request->validate([
            'counter_notice_text' => 'required|string|max:5000',
            'counter_notice_evidence_urls' => 'nullable|array',
            'counter_notice_evidence_urls.*' => 'nullable|url',
        ]);

        $oldStatus = $report->status;

        $report->update([
            'counter_notice_text' => $validated['counter_notice_text'],
            'counter_notice_evidence_urls' => $validated['counter_notice_evidence_urls'] ?? [],
            'counter_submitted_at' => now(),
            'status' => 'under_review',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'counter_notice_submitted',
            'target_type' => 'Report',
            'target_id' => $report->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'under_review',
                'counter_submitted_at' => $report->counter_submitted_at,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Thông Báo Phản Đối (Counter-Notice) của bạn đã được ghi nhận. Hệ thống đang tiến hành đối soát theo Nghị định 17/2023/NĐ-CP.');
    }

    /**
     * Ensure the authenticated user is the legitimate author of the reported work.
     */
    protected function authorizeAuthor(Report $report): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        $novel = $this->resolveNovel($report);

        if (!$novel || $novel->author_id !== $user->id) {
            abort(403, 'Bạn không có quyền gửi thông báo phản đối cho tác phẩm này.');
        }
    }

    /**
     * Resolve the novel associated with the report.
     */
    protected function resolveNovel(Report $report): ?Novel
    {
        if ($report->reportable_type === 'Novel') {
            return Novel::find($report->reportable_id);
        }

        if ($report->reportable_type === 'Chapter') {
            $chapter = Chapter::find($report->reportable_id);
            return $chapter?->novel;
        }

        return null;
    }
}
