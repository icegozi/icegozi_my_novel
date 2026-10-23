<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogExplorerController extends Controller
{
    /**
     * Display searchable audit trail for cybersecurity inspections.
     */
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        $logs = $query->paginate(25)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit_logs.index', compact('logs', 'actions'));
    }

    /**
     * Export audit logs to JSON for external cybersecurity compliance audits (ND 147/2024).
     */
    public function export(Request $request): JsonResponse
    {
        $logs = $this->buildFilteredQuery($request)->get();

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.json';

        return response()->json($logs, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Build filtered query based on IP, action, target_type, and dates.
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . trim($request->input('ip_address')) . '%');
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->input('target_type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return $query;
    }
}
