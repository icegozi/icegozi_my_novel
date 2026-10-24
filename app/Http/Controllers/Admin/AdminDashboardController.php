<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display Admin CMS Dashboard overview with real-time KPI metrics.
     */
    public function index(Request $request): View
    {
        $stats = [
            'total_novels' => Novel::count(),
            'published_novels' => Novel::where('status', 'published')->count(),
            'pending_novels' => Novel::where('status', 'pending_approval')->count(),
            'total_chapters' => Chapter::count(),
            'pending_chapters' => Chapter::where('status', 'pending_approval')->count(),
            'verified_authors' => AuthorProfile::where('verification_status', 'verified')->count(),
            'pending_kyc' => AuthorProfile::where('verification_status', 'pending')->count(),
            'total_users' => User::count(),
            'pending_reports' => Report::whereIn('status', ['received', 'under_review'])->count(),
        ];

        $recentNovels = Novel::with(['author.authorProfile', 'genres'])
            ->latest()
            ->take(5)
            ->get();

        $recentAuditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentNovels', 'recentAuditLogs'));
    }
}
