<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount(['novels' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        return view('reader.genres.index', compact('genres'));
    }

    public function show(string $slug)
    {
        $genre = Genre::where('slug', $slug)->firstOrFail();

        $novels = $genre->novels()
            ->where('status', 'published')
            ->with(['author', 'genres', 'chapters'])
            ->orderByDesc('rating_avg')
            ->paginate(12);

        return view('reader.genres.show', compact('genre', 'novels'));
    }
}
