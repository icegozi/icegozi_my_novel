<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NovelManagementController extends Controller
{
    /**
     * Display a paginated, filterable list of all novels.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAnyAdmin', Novel::class);

        $query = Novel::with(['author.authorProfile', 'genres'])
            ->withCount('chapters')
            ->latest();

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('slug', 'like', "%{$term}%")
                  ->orWhereHas('author.authorProfile', function ($aq) use ($term) {
                      $aq->where('pen_name', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');
            $query->whereHas('genres', function ($gq) use ($genreId) {
                $gq->where('genres.id', $genreId);
            });
        }

        $novels = $query->paginate(15)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        return view('admin.novels.index', compact('novels', 'genres'));
    }

    /**
     * Show the edit form for a novel including SEO and metadata.
     */
    public function edit(Novel $novel): View
    {
        Gate::authorize('updateAdmin', $novel);

        $novel->load(['genres', 'tags', 'author.authorProfile']);
        $allGenres = Genre::orderBy('name')->get();
        $allTags = Tag::orderBy('name')->get();

        return view('admin.novels.edit', compact('novel', 'allGenres', 'allTags'));
    }

    /**
     * Update the specified novel.
     */
    public function update(Request $request, Novel $novel): RedirectResponse
    {
        Gate::authorize('updateAdmin', $novel);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'required|string',
            'status' => 'required|in:draft,pending_approval,published,rejected,paused,completed,takedown,archived',
            'age_rating' => 'required|in:all,13_plus,16_plus,18_plus',
            'copyright_type' => 'required|in:original,authorized_translation,public_domain',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'og_image' => 'nullable|string|max:255',
            'genres' => 'nullable|array',
            'genres.*' => 'integer|exists:genres,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $oldValues = $novel->only([
            'title', 'status', 'age_rating', 'copyright_type',
            'meta_title', 'meta_description', 'canonical_url', 'og_image'
        ]);

        $novel->update([
            'title' => $validated['title'],
            'synopsis' => $validated['synopsis'],
            'status' => $validated['status'],
            'age_rating' => $validated['age_rating'],
            'copyright_type' => $validated['copyright_type'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ]);

        if (isset($validated['genres'])) {
            $novel->genres()->sync($validated['genres']);
        }

        if (isset($validated['tags'])) {
            $novel->tags()->sync($validated['tags']);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_novel_updated',
            'target_type' => 'Novel',
            'target_id' => $novel->id,
            'old_values' => $oldValues,
            'new_values' => $novel->only(array_keys($oldValues)),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.novels.edit', $novel)
            ->with('success', "Đã cập nhật tác phẩm '{$novel->title}' thành công.");
    }

    /**
     * Soft delete the specified novel.
     */
    public function destroy(Request $request, Novel $novel): RedirectResponse
    {
        Gate::authorize('deleteAdmin', $novel);

        $novelTitle = $novel->title;
        $novelId = $novel->id;

        $novel->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_novel_soft_deleted',
            'target_type' => 'Novel',
            'target_id' => $novelId,
            'old_values' => ['title' => $novelTitle],
            'new_values' => ['deleted_at' => now()->toIso8601String()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.novels.index')
            ->with('success', "Đã xóa mềm tác phẩm '{$novelTitle}' khỏi hệ thống hiển thị.");
    }
}
