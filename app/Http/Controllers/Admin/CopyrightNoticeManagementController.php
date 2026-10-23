<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NovelTakedownNoticeMail;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CopyrightNoticeManagementController extends Controller
{
    /**
     * Display list of copyright reports and DMCA claims.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = Report::query()->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(20);

        return view('admin.copyright.index', compact('reports'));
    }

    /**
     * View detailed copyright claim and investigation dossier.
     */
    public function show(Report $report): View
    {
        $targetNovel = null;
        $targetChapter = null;

        if ($report->reportable_type === 'Novel') {
            $targetNovel = Novel::with(['author.authorProfile'])->find($report->reportable_id);
        } elseif ($report->reportable_type === 'Chapter') {
            $targetChapter = Chapter::with(['novel.author.authorProfile'])->find($report->reportable_id);
            $targetNovel = $targetChapter?->novel;
        }

        return view('admin.copyright.show', compact('report', 'targetNovel', 'targetChapter'));
    }

    /**
     * Issue temporary takedown under ND 17/2023/ND-CP.
     */
    public function temporaryTakedown(Request $request, Report $report): RedirectResponse
    {
        $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $report->status;

        $report->update([
            'status' => 'temporary_takedown',
            'resolution_notes' => $request->input('resolution_notes', 'Tạm ẩn tác phẩm theo quy định Nghị định 17/2023/NĐ-CP.'),
            'resolved_by' => auth()->id(),
        ]);

        if ($report->reportable_type === 'Novel') {
            Novel::where('id', $report->reportable_id)->update(['status' => 'takedown']);
        } elseif ($report->reportable_type === 'Chapter') {
            Chapter::where('id', $report->reportable_id)->update(['status' => 'rejected']);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'copyright_temporary_takedown',
            'target_type' => 'Report',
            'target_id' => $report->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'temporary_takedown',
                'target_type' => $report->reportable_type,
                'target_id' => $report->reportable_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Send legal takedown notice email to author under ND 17/2023
        $novel = null;
        if ($report->reportable_type === 'Novel') {
            $novel = Novel::with('author')->find($report->reportable_id);
        } elseif ($report->reportable_type === 'Chapter') {
            $chapter = Chapter::with('novel.author')->find($report->reportable_id);
            $novel = $chapter?->novel;
        }

        if ($novel && $novel->author && $novel->author->email) {
            Mail::to($novel->author->email)->send(new NovelTakedownNoticeMail($novel, $report));
        }

        return redirect()->route('admin.copyright.show', $report)
            ->with('warning', 'Đã tạm ẩn tác phẩm và kích hoạt thời hạn 10 ngày làm việc để tác giả nộp Thông Báo Phản Đối.');
    }

    /**
     * Restore work after counter-notice review when complainant fails to file court lawsuit.
     */
    public function restore(Request $request, Report $report): RedirectResponse
    {
        $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $report->status;

        $report->update([
            'status' => 'resolved',
            'resolution_notes' => $request->input('resolution_notes', 'Khôi phục tác phẩm sau khi xem xét Thông Báo Phản Đối.'),
            'resolved_by' => auth()->id(),
        ]);

        if ($report->reportable_type === 'Novel') {
            Novel::where('id', $report->reportable_id)->update(['status' => 'published']);
        } elseif ($report->reportable_type === 'Chapter') {
            Chapter::where('id', $report->reportable_id)->update(['status' => 'published']);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'copyright_work_restored',
            'target_type' => 'Report',
            'target_id' => $report->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'resolved',
                'restored_at' => now(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.copyright.show', $report)
            ->with('success', 'Đã phục hồi tác phẩm công khai thành công.');
    }

    /**
     * Dismiss frivolous or unsubstantiated copyright claim.
     */
    public function dismiss(Request $request, Report $report): RedirectResponse
    {
        $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $report->status;

        $report->update([
            'status' => 'dismissed',
            'resolution_notes' => $request->input('resolution_notes', 'Bác đơn khiếu nại do không có đủ căn cứ pháp lý.'),
            'resolved_by' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'copyright_report_dismissed',
            'target_type' => 'Report',
            'target_id' => $report->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'dismissed',
                'reason' => $report->resolution_notes,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.copyright.show', $report)
            ->with('info', 'Đơn khiếu nại đã được phân loại bác bỏ (dismissed).');
    }
}
