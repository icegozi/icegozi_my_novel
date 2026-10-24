<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNovelRequest;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NovelController extends Controller
{
    /**
     * Display a listing of novels belonging to the authenticated author.
     */
    public function index(Request $request): View
    {
        $novels = Novel::where('author_id', $request->user()->id)
            ->with(['genres', 'chapters'])
            ->latest()
            ->paginate(15);

        return view('author.novels.index', compact('novels'));
    }

    /**
     * Show the form for creating a new novel.
     */
    public function create(): View
    {
        $genres = Genre::all();
        $novel = new Novel();

        return view('author.novels.create_or_edit', compact('genres', 'novel'));
    }

    /**
     * Store a newly created novel with copyright declaration.
     */
    public function store(StoreNovelRequest $request): RedirectResponse
    {
        $author = $request->user();

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = Storage::disk('public')->putFile('covers', $request->file('cover_image'));
        }

        $baseSlug = Str::slug($request->input('title'));
        $slug = $baseSlug . '-' . substr(uniqid(), -5);

        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => $request->input('title'),
            'slug' => $slug,
            'synopsis' => $request->input('synopsis'),
            'cover_image' => $coverPath,
            'status' => 'draft',
            'age_rating' => $request->input('age_rating'),
            'copyright_type' => $request->input('copyright_type'),
            'copyright_license_notes' => $request->input('copyright_license_notes'),
            'content_warnings' => $request->input('content_warnings', []),
        ]);

        $novel->genres()->sync($request->input('genres', []));

        return redirect('/tac-gia/truyen')
            ->with('success', "Tác phẩm \"{$novel->title}\" đã được tạo thành công!");
    }

    /**
     * Show the form for editing the specified novel.
     */
    public function edit(Request $request, Novel $truyen): View
    {
        abort_if($truyen->author_id !== $request->user()->id, 403, 'Bạn không có quyền chỉnh sửa tác phẩm này.');

        $truyen->load(['chapters' => fn($q) => $q->orderBy('chapter_number', 'asc')]);

        $genres = Genre::all();
        $novel = $truyen;

        return view('author.novels.create_or_edit', compact('genres', 'novel'));
    }

    /**
     * Update the specified novel in storage.
     */
    public function update(StoreNovelRequest $request, Novel $truyen): RedirectResponse
    {
        abort_if($truyen->author_id !== $request->user()->id, 403, 'Bạn không có quyền cập nhật tác phẩm này.');

        if ($request->hasFile('cover_image')) {
            if ($truyen->cover_image && Storage::disk('public')->exists($truyen->cover_image)) {
                Storage::disk('public')->delete($truyen->cover_image);
            }
            $truyen->cover_image = Storage::disk('public')->putFile('covers', $request->file('cover_image'));
        }

        $truyen->update([
            'title' => $request->input('title'),
            'synopsis' => $request->input('synopsis'),
            'age_rating' => $request->input('age_rating'),
            'copyright_type' => $request->input('copyright_type'),
            'copyright_license_notes' => $request->input('copyright_license_notes'),
            'content_warnings' => $request->input('content_warnings', []),
        ]);

        $truyen->genres()->sync($request->input('genres', []));

        return redirect('/tac-gia/truyen')
            ->with('success', "Cập nhật tác phẩm \"{$truyen->title}\" thành công!");
    }

    /**
     * Remove the specified novel from storage.
     */
    public function destroy(Request $request, Novel $truyen): RedirectResponse
    {
        abort_if($truyen->author_id !== $request->user()->id, 403, 'Bạn không có quyền xóa tác phẩm này.');

        if ($truyen->cover_image && Storage::disk('public')->exists($truyen->cover_image)) {
            Storage::disk('public')->delete($truyen->cover_image);
        }

        $truyen->delete();

        return redirect('/tac-gia/truyen')
            ->with('success', 'Đã xóa tác phẩm thành công.');
    }
}
