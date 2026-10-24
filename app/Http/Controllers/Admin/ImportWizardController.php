<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportJob;
use App\Services\ImportValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportWizardController extends Controller
{
    /**
     * Ensure only super admins can execute bulk imports.
     */
    protected function authorizeAdmin(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Chỉ Quản trị viên cấp cao mới có quyền truy cập tác vụ nhập dữ liệu.');
        }
    }

    /**
     * Step 1: Display Import Wizard Desk with download templates and upload form.
     */
    public function index(): View
    {
        $this->authorizeAdmin();

        $recentJobs = ImportJob::with('user')->latest()->take(10)->get();

        return view('admin.import.index', compact('recentJobs'));
    }

    /**
     * Download standard UTF-8 BOM CSV template.
     */
    public function template(string $type, ImportValidationService $service): StreamedResponse
    {
        $this->authorizeAdmin();

        if (!in_array($type, ['novels', 'chapters'])) {
            abort(404);
        }

        $templateContent = $service->generateTemplate($type);
        $filename = "template_import_{$type}.csv";

        return response()->streamDownload(function () use ($templateContent) {
            echo $templateContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Step 2 & 3: Upload file and execute Dry-run Validation.
     */
    public function upload(Request $request, ImportValidationService $service): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'type' => 'required|in:novels,chapters',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $idempKey = hash('sha256', auth()->id() . $file->getClientOriginalName() . $file->getSize() . microtime(true));

        $path = Storage::disk('local')->putFile('imports', $file);
        $fullPath = Storage::disk('local')->path($path);

        $job = ImportJob::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'validating',
            'dry_run' => true,
            'idempotency_key' => $idempKey,
        ]);

        $result = $service->validateCsv($fullPath, $validated['type']);

        if (!$result['valid']) {
            $errorReportCsv = $service->generateErrorReportCsv($result['errors']);
            $errorReportPath = "imports/errors_{$job->id}.csv";
            Storage::disk('local')->put($errorReportPath, $errorReportCsv);

            $job->update([
                'status' => 'failed',
                'error_summary' => $result['errors'],
                'error_report_file' => $errorReportPath,
                'total_rows' => $result['total_rows'],
                'failed_rows' => count($result['errors']),
            ]);
        } else {
            $job->update([
                'status' => 'validated',
                'total_rows' => $result['total_rows'],
                'successful_rows' => count($result['parsed_data']),
            ]);
        }

        return redirect()->route('admin.import.preview', $job);
    }

    /**
     * Step 3: Preview Dry-run results, display error breakdown, and prepare commit.
     */
    public function preview(ImportJob $job): View
    {
        $this->authorizeAdmin();

        return view('admin.import.preview', compact('job'));
    }

    /**
     * Download CSV detailed error report.
     */
    public function errors(ImportJob $job, ImportValidationService $service): StreamedResponse
    {
        $this->authorizeAdmin();

        $errors = $job->error_summary ?? [];
        $csvContent = $service->generateErrorReportCsv($errors);
        $filename = "bao_cao_loi_import_job_{$job->id}.csv";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Step 4: Commit validated import job data into database.
     */
    public function commit(ImportJob $job, ImportValidationService $service): RedirectResponse
    {
        $this->authorizeAdmin();

        // Atomic status transition prevents race condition / double-submission
        $affected = ImportJob::where('id', $job->id)
            ->where('status', 'validated')
            ->update(['status' => 'importing']);

        if (!$affected) {
            return redirect()->route('admin.import.preview', $job)
                ->with('error', 'Tệp dữ liệu này chưa vượt qua vòng kiểm tra Dry-Run, đang được xử lý hoặc đã hoàn tất.');
        }

        $fullPath = Storage::disk('local')->path($job->file_path);
        $result = $service->validateCsv($fullPath, $job->type);

        if (!$result['valid']) {
            $job->update(['status' => 'failed']);
            return redirect()->route('admin.import.preview', $job)
                ->with('error', 'Không thể hoàn tất nạp dữ liệu vì tệp chứa lỗi.');
        }

        $count = $service->commitImport($job, $result['parsed_data'], auth()->user());

        return redirect()->route('admin.import.index')
            ->with('success', "Đã nạp thành công {$count} bản ghi vào hệ thống dưới dạng bản nháp (Draft).");
    }
}
