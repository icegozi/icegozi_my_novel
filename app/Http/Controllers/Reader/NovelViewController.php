<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Novel;
use Illuminate\View\View;

class NovelViewController extends Controller
{
    /**
     * Display public novel details, synopsis, chapters, and 3D assets showcase.
     */
    public function show(string $slug): View
    {
        $novel = Novel::where('slug', $slug)
            ->with([
                'author.authorProfile',
                'genres',
                'assets3d',
                'chapters' => function ($query) {
                    $query->where('status', 'published')->orderBy('chapter_number');
                },
            ])
            ->first();

        if (! $novel) {
            abort(404);
        }

        // Only published novels can be viewed by public guests unless the viewer is the author or admin
        if ($novel->status !== 'published') {
            $user = auth()->user();
            if (! $user || ($user->id !== $novel->author_id && $user->role !== 'admin')) {
                abort(404);
            }
        }

        return view('reader.novels.show', compact('novel'));
    }

    /**
     * Display dedicated 3D Exhibition Stage for all assets of the novel.
     */
    public function exhibition(string $slug): View
    {
        $novel = Novel::where('slug', $slug)
            ->with(['author.authorProfile', 'assets3d'])
            ->firstOrFail();

        if ($novel->status !== 'published') {
            $user = auth()->user();
            if (! $user || ($user->id !== $novel->author_id && $user->role !== 'admin')) {
                abort(404);
            }
        }

        return view('reader.novels.3d_exhibition', compact('novel'));
    }
}
