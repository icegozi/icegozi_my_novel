<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Novel;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLibraryController extends Controller
{
    public function library(Request $request)
    {
        $user = Auth::user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['novel.genres', 'novel.chapters'])
            ->get();

        $histories = ReadingHistory::where('user_id', $user->id)
            ->with(['novel.genres', 'lastChapter'])
            ->orderByDesc('updated_at')
            ->get();

        return view('reader.user.library', compact('bookmarks', 'histories'));
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $histories = ReadingHistory::where('user_id', $user->id)
            ->with(['novel.genres', 'novel.author', 'lastChapter'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('reader.user.history', compact('histories'));
    }

    public function bookmarks(Request $request)
    {
        $user = Auth::user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['novel.author', 'novel.genres', 'novel.chapters'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('reader.user.bookmarks', compact('bookmarks'));
    }

    public function toggleBookmark(Request $request)
    {
        $request->validate([
            'novel_id' => ['required', 'exists:novels,id'],
        ]);

        $user = Auth::user();
        $novelId = $request->input('novel_id');
        $novel = Novel::findOrFail($novelId);

        $existing = Bookmark::where('user_id', $user->id)->where('novel_id', $novelId)->first();

        if ($existing) {
            $existing->delete();
            $novel->decrement('bookmark_count');
            $bookmarked = false;
            $message = 'Đã xóa khỏi Tủ Sách cá nhân.';
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'novel_id' => $novelId,
                'created_at' => now(),
            ]);
            $novel->increment('bookmark_count');
            $bookmarked = true;
            $message = 'Đã lưu tác phẩm vào Tủ Sách thành công!';
        }

        return response()->json([
            'status' => 'ok',
            'bookmarked' => $bookmarked,
            'message' => $message,
            'bookmark_count' => $novel->bookmark_count,
        ]);
    }

    public function deleteHistory(int $id)
    {
        $history = ReadingHistory::findOrFail($id);
        if ($history->user_id !== Auth::id()) {
            abort(403);
        }
        $history->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Đã xóa tác phẩm khỏi lịch sử đọc.',
        ]);
    }
}
