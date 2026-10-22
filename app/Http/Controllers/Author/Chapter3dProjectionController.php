<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Chapter3dProjection;
use App\Models\Novel3dAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Chapter3dProjectionController extends Controller
{
    public function index(Chapter $chapter): JsonResponse
    {
        $this->authorizeChapter($chapter);

        $projections = $chapter->projections3d()
            ->with('asset3d')
            ->orderBy('paragraph_index')
            ->get();

        return response()->json($projections);
    }

    public function store(Request $request, Chapter $chapter): JsonResponse
    {
        $this->authorizeChapter($chapter);

        $validated = $request->validate([
            'asset_3d_id' => ['required', 'exists:novel_3d_assets,id'],
            'paragraph_index' => ['required', 'integer', 'min:0'],
            'trigger_keyword' => ['nullable', 'string', 'max:100'],
            'hologram_title' => ['required', 'string', 'max:150'],
            'auto_rotate' => ['nullable', 'boolean'],
            'display_mode' => ['nullable', 'in:inline_box,split_stage,hologram_overlay,background_ambience'],
        ]);

        // Ensure asset belongs to the same novel
        $asset = Novel3dAsset::findOrFail($validated['asset_3d_id']);
        if ($asset->novel_id !== $chapter->novel_id) {
            abort(422, 'Mô hình 3D không thuộc tiểu thuyết này.');
        }

        $projection = $chapter->projections3d()->create([
            'asset_3d_id' => $validated['asset_3d_id'],
            'paragraph_index' => $validated['paragraph_index'],
            'trigger_keyword' => $validated['trigger_keyword'] ?? null,
            'hologram_title' => $validated['hologram_title'],
            'auto_rotate' => $validated['auto_rotate'] ?? true,
            'display_mode' => $validated['display_mode'] ?? 'split_stage',
        ]);

        $projection->load('asset3d');

        return response()->json([
            'message' => 'Đã liên kết mô hình 3D vào đoạn văn thành công!',
            'projection' => $projection,
        ], 201);
    }

    public function destroy(Chapter $chapter, Chapter3dProjection $projection): JsonResponse
    {
        $this->authorizeChapter($chapter);

        if ($projection->chapter_id !== $chapter->id) {
            abort(404);
        }

        $projection->delete();

        return response()->json([
            'message' => 'Đã xóa liên kết mô hình 3D khỏi đoạn văn.',
        ]);
    }

    protected function authorizeChapter(Chapter $chapter): void
    {
        $novel = $chapter->novel;
        if (! $novel || ($novel->author_id !== auth()->id() && auth()->user()?->role !== 'admin')) {
            abort(403, 'Bạn không có quyền quản lý hiệu ứng 3D của chương này.');
        }
    }
}
