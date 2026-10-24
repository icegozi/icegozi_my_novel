<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ExportJob;
use App\Models\Genre;
use App\Services\DataStreamingExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Ensure only admin and moderator can access export features.
     */
    protected function authorizeAccess(): void
    {
        if (!in_array(auth()->user()?->role, ['admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền truy cập tính năng xuất dữ liệu báo cáo.');
        }
    }

    /**
     * Display the export desk with filtering options and column selectors.
     */
    public function index(): View
    {
        $this->authorizeAccess();

        $genres = Genre::orderBy('name')->get();
        $novelColumns = DataStreamingExportService::NOVEL_COLUMNS;
        $authorColumns = DataStreamingExportService::AUTHOR_COLUMNS;

        $recentJobs = ExportJob::with('user')->latest()->take(10)->get();

        return view('admin.export.index', compact('genres', 'novelColumns', 'authorColumns', 'recentJobs'));
    }

    /**
     * Generate and stream the exported CSV file directly to the client.
     */
    public function download(Request $request, DataStreamingExportService $service): StreamedResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'type' => 'required|in:novels,authors',
            'status' => 'nullable|string|max:50',
            'genre_id' => 'nullable|integer|exists:genres,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'verification_status' => 'nullable|string|in:pending,verified,rejected',
            'user_status' => 'nullable|string|in:active,suspended,banned',
            'columns' => 'nullable|array',
            'columns.*' => 'string|max:50',
        ]);

        $filters = array_filter($request->only(['status', 'genre_id', 'from_date', 'to_date', 'verification_status', 'user_status']));
        $selectedColumns = $validated['columns'] ?? [];

        // Record Export Job in database
        $job = ExportJob::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'filters' => $filters,
            'columns' => $selectedColumns,
            'status' => 'completed',
        ]);

        // Record Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_data_exported',
            'target_type' => 'ExportJob',
            'target_id' => $job->id,
            'old_values' => [],
            'new_values' => [
                'type' => $validated['type'],
                'filters' => $filters,
                'columns' => $selectedColumns,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($validated['type'] === 'authors') {
            return $service->streamAuthors($filters, $selectedColumns);
        }

        return $service->streamNovels($filters, $selectedColumns);
    }
}
