<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Novel::where('status', 'published')
            ->with(['author', 'genres', 'assets3d', 'chapters'])
            ->orderByDesc('rating_avg')
            ->first();

        $trendingNovels = Novel::where('status', 'published')
            ->with(['author', 'genres'])
            ->orderByDesc('view_count')
            ->take(6)
            ->get();

        $latestChapters = Chapter::where('status', 'published')
            ->with(['novel.author', 'novel.genres'])
            ->whereHas('novel', function ($q) {
                $q->where('status', 'published');
            })
            ->orderByDesc('published_at')
            ->take(8)
            ->get();

        $genres = Genre::withCount(['novels' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        $continueReading = null;
        if (Auth::check()) {
            $continueReading = Auth::user()->readingHistories()
                ->with(['novel', 'lastChapter'])
                ->orderByDesc('updated_at')
                ->take(3)
                ->get();
        }

        return view('reader.home', compact(
            'featured',
            'trendingNovels',
            'latestChapters',
            'genres',
            'continueReading'
        ));
    }
}
