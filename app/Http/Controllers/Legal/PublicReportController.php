<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    /**
     * Show report form.
     */
    public function create(Request $request): View
    {
        $novel = null;
        if ($request->has('novel_id')) {
            $novel = Novel::find($request->input('novel_id'));
        }

        return view('legal.copyright_report_form', compact('novel'));
    }

    /**
     * Store public copyright infringement or policy violation report.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reportable_type' => 'required|string|in:Novel,Chapter',
            'reportable_id' => 'required|integer',
            'reporter_email' => 'required|email|max:191',
            'violation_category' => 'required|string|in:copyright_infringement,violates_national_security,pornography,violence_hate_speech,defamation',
            'description' => 'required|string|max:5000',
            'evidence_urls' => 'nullable|array',
            'evidence_urls.*' => 'nullable|url',
        ]);

        $report = Report::create([
            'reporter_id' => auth()->id(),
            'reporter_email' => $validated['reporter_email'],
            'reportable_type' => $validated['reportable_type'],
            'reportable_id' => $validated['reportable_id'],
            'violation_category' => $validated['violation_category'],
            'description' => $validated['description'],
            'evidence_urls' => $validated['evidence_urls'] ?? [],
            'status' => 'received',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'report_submitted',
            'target_type' => $validated['reportable_type'],
            'target_id' => $validated['reportable_id'],
            'old_values' => null,
            'new_values' => [
                'report_id' => $report->id,
                'violation_category' => $report->violation_category,
                'reporter_email' => $report->reporter_email,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Thông báo khiếu nại đã được tiếp nhận. Đội đồng quản trị sẽ xử lý theo quy định tại Nghị định 17/2023/NĐ-CP.');
    }
}
