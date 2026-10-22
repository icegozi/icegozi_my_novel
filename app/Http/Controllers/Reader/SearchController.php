<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Novel::where('status', 'published')
            ->with(['author', 'genres', 'chapters']);

        // Keyword filter
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('synopsis', 'like', "%{$keyword}%")
                  ->orWhereHas('author', function ($authorQ) use ($keyword) {
                      $authorQ->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // Genre filter
        if ($request->filled('genre') && $request->input('genre') !== 'all') {
            $genreSlug = $request->input('genre');
            $query->whereHas('genres', function ($gQ) use ($genreSlug) {
                $gQ->where('slug', $genreSlug);
            });
        }

        // Age rating filter
        if ($request->filled('age_rating') && $request->input('age_rating') !== 'all') {
            $query->where('age_rating', $request->input('age_rating'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'views':
            case 'popular':
                $query->orderByDesc('view_count');
                break;
            case 'rating':
                $query->orderByDesc('rating_avg');
                break;
            case 'chapters':
                $query->orderByDesc('total_chapters');
                break;
            case 'latest':
            default:
                $query->orderByDesc('updated_at');
                break;
        }

        $novels = $query->paginate(12)->withQueryString();
        $genres = Genre::all();

        return view('reader.search', compact('novels', 'genres'));
    }
}
