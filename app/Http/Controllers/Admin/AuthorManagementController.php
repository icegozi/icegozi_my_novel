<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuthorManagementController extends Controller
{
    /**
     * Display searchable and filterable list of author profiles.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AuthorProfile::class);

        $query = AuthorProfile::with(['user' => function ($q) {
            $q->withCount('novels');
        }])->latest();

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('pen_name', 'like', "%{$term}%")
                  ->orWhere('legal_full_name', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('email', 'like', "%{$term}%")
                         ->orWhere('name', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        if ($request->filled('user_status')) {
            $query->whereHas('user', function ($uq) use ($request) {
                $uq->where('status', $request->input('user_status'));
            });
        }

        $authors = $query->paginate(15)->withQueryString();

        return view('admin.authors.index', compact('authors'));
    }

    /**
     * Display detailed dossier of a specific author and their novel portfolio.
     */
    public function show(AuthorProfile $author): View
    {
        Gate::authorize('view', $author);

        $author->load(['user.novels' => function ($q) {
            $q->withCount('chapters')->latest();
        }]);

        return view('admin.authors.show', compact('author'));
    }

    /**
     * Show form for editing an author's metadata.
     */
    public function edit(AuthorProfile $author): View
    {
        Gate::authorize('update', $author);

        return view('admin.authors.edit', compact('author'));
    }

    /**
     * Update author profile details.
     */
    public function update(Request $request, AuthorProfile $author): RedirectResponse
    {
        Gate::authorize('update', $author);

        $validated = $request->validate([
            'pen_name' => 'required|string|max:100',
            'tax_code' => 'nullable|string|max:30',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
        ]);

        $oldValues = $author->only(['pen_name', 'tax_code', 'bank_name', 'bank_account_number']);

        $author->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'author_profile_updated',
            'target_type' => 'AuthorProfile',
            'target_id' => $author->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.authors.show', $author)
            ->with('success', "Đã cập nhật thông tin tác giả '{$author->pen_name}' thành công.");
    }

    /**
     * Suspend, ban, or activate an author user account.
     */
    public function changeStatus(Request $request, AuthorProfile $author): RedirectResponse
    {
        Gate::authorize('changeStatus', $author->user);

        $validated = $request->validate([
            'status' => 'required|in:active,suspended,banned',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $author->user->status;

        $author->user->update([
            'status' => $validated['status'],
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'author_status_changed',
            'target_type' => 'User',
            'target_id' => $author->user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $validated['status'],
                'reason' => $validated['reason'] ?? 'Không có lý do',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.authors.show', $author)
            ->with('success', "Đã chuyển trạng thái tài khoản tác giả thành '{$validated['status']}'.");
    }
}
