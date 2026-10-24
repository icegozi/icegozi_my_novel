<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Services\ChapterVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChapterVersionController extends Controller
{
    /**
     * Display revision history of a chapter with interactive side-by-side or unified diff.
     */
    public function history(Request $request, Chapter $chapter, ChapterVersionService $diffService): View
    {
        Gate::authorize('viewHistory', $chapter);

        $chapter->load('novel');

        $versions = $chapter->versions()
            ->with('editor')
            ->orderBy('revision', 'desc')
            ->get();

        $diff = null;
        $fromVersion = null;
        $toVersion = null;

        if ($request->filled('compare_from') && $request->filled('compare_to')) {
            $fromVersion = $versions->firstWhere('id', (int) $request->input('compare_from'));
            $toVersion = $versions->firstWhere('id', (int) $request->input('compare_to'));

            if ($fromVersion && $toVersion) {
                $diff = $diffService->diff($fromVersion->content, $toVersion->content);
            }
        } elseif ($request->filled('compare_version')) {
            // Compare historical version against current live chapter
            $fromVersion = $versions->firstWhere('id', (int) $request->input('compare_version'));
            if ($fromVersion) {
                $diff = $diffService->diff($fromVersion->content, $chapter->content);
            }
        }

        return view('admin.chapters.history', compact('chapter', 'versions', 'diff', 'fromVersion', 'toVersion'));
    }

    /**
     * Restore chapter content to a selected historical version.
     */
    public function restore(
        Request $request,
        Chapter $chapter,
        ChapterVersion $version,
        ChapterVersionService $versionService
    ): RedirectResponse {
        Gate::authorize('restoreHistory', $chapter);

        $versionService->restore($chapter, $version, auth()->user());

        return redirect()->route('admin.chapters.history', $chapter)
            ->with('success', "Đã khôi phục nội dung chương về phiên bản rev #{$version->revision} thành công.");
    }
}
