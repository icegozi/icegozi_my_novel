<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\View\View;

class ChapterReaderController extends Controller
{
    /**
     * Display chapter text in 3D Interactive Stage reader mode.
     */
    public function read(string $slug, float $chapterNumber): View
    {
        $novel = Novel::where('slug', $slug)
            ->with(['author.authorProfile', 'assets3d'])
            ->first();

        if (! $novel) {
            abort(404);
        }

        $user = auth()->user();
        $isPrivileged = $user && ($user->id === $novel->author_id || $user->role === 'admin');

        if ($novel->status !== 'published' && ! $isPrivileged) {
            abort(404);
        }

        $chapterQuery = Chapter::where('novel_id', $novel->id)
            ->where('chapter_number', $chapterNumber)
            ->with(['projections3d.asset3d']);

        if (! $isPrivileged) {
            $chapterQuery->where('status', 'published');
        }

        $chapter = $chapterQuery->first();

        if (! $chapter) {
            abort(404);
        }

        // Increment view count
        $chapter->increment('view_count');
        $novel->increment('view_count');

        // Navigation links
        $previousChapter = Chapter::where('novel_id', $novel->id)
            ->where('status', 'published')
            ->where('chapter_number', '<', $chapter->chapter_number)
            ->orderByDesc('chapter_number')
            ->first();

        $nextChapter = Chapter::where('novel_id', $novel->id)
            ->where('status', 'published')
            ->where('chapter_number', '>', $chapter->chapter_number)
            ->orderBy('chapter_number')
            ->first();

        return view('reader.chapters.read_3d', compact('novel', 'chapter', 'previousChapter', 'nextChapter'));
    }
}
