<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function latest(Request $request)
    {
        $chapters = Chapter::where('status', 'published')
            ->with(['novel.author', 'novel.genres'])
            ->whereHas('novel', function ($q) {
                $q->where('status', 'published');
            })
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('reader.catalog.latest', compact('chapters'));
    }

    public function popular(Request $request)
    {
        $novels = Novel::where('status', 'published')
            ->with(['author', 'genres', 'chapters'])
            ->orderByDesc('view_count')
            ->paginate(12);

        return view('reader.catalog.popular', compact('novels'));
    }
}
