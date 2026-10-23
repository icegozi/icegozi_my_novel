<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Novel;
use App\Services\ChapterSplitterService;
use App\Services\ContentSanitizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BulkChapterController extends Controller
{
    public function create(Novel $novel): View
    {
        $this->authorizeNovel($novel);

        return view('author.chapters.bulk_splitter', compact('novel'));
    }

    public function preview(Request $request, Novel $novel, ChapterSplitterService $splitter): JsonResponse
    {
        $this->authorizeNovel($novel);

        $request->validate([
            'raw_text' => 'required|string',
        ]);

        $chapters = $splitter->split($request->input('raw_text'));

        return response()->json([
            'total' => count($chapters),
            'chapters' => $chapters,
        ]);
    }

    public function store(
        Request $request,
        Novel $novel,
        ChapterSplitterService $splitter,
        ContentSanitizerService $sanitizer
    ): RedirectResponse {
        $this->authorizeNovel($novel);

        $request->validate([
            'raw_text' => 'required|string',
        ]);

        $parsed = $splitter->split($request->input('raw_text'));

        if (empty($parsed)) {
            return back()->withErrors(['raw_text' => 'Không tìm thấy chương nào trong văn bản đã dán.']);
        }

        DB::transaction(function () use ($parsed, $novel, $sanitizer) {
            $existingNumbers = $novel->chapters()->pluck('chapter_number')->map(fn($n) => (float)$n)->all();
            $maxNumber = (float) ($novel->chapters()->max('chapter_number') ?? 0);

            foreach ($parsed as $item) {
                $targetNumber = $item['chapter_number'];
                if (in_array($targetNumber, $existingNumbers, true)) {
                    $maxNumber += 1.0;
                    $targetNumber = $maxNumber;
                } else {
                    $maxNumber = max($maxNumber, $targetNumber);
                }
                $existingNumbers[] = $targetNumber;

                $cleanContent = $sanitizer->clean($item['content']);
                $wordCount = $sanitizer->countWords($cleanContent);

                Chapter::create([
                    'novel_id' => $novel->id,
                    'chapter_number' => $targetNumber,
                    'title' => $item['title'],
                    'slug' => Str::slug($item['title']) . '-' . uniqid(),
                    'content' => $cleanContent,
                    'word_count' => $wordCount,
                    'status' => 'draft',
                    'revision' => 1,
                ]);
            }

            $novel->total_chapters = $novel->chapters()->count();
            $novel->total_words = $novel->chapters()->sum('word_count');
            $novel->save();
        });

        return redirect()->route('author.novels.index')
            ->with('status', 'Đã tách và lưu thành công ' . count($parsed) . ' chương vào bản nháp!');
    }

    protected function authorizeNovel(Novel $novel): void
    {
        if ($novel->author_id !== auth()->id() && auth()->user()?->role !== 'admin') {
            abort(403, 'Bạn không có quyền quản lý tiểu thuyết này.');
        }
    }
}
