<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload3dAssetRequest;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class Novel3dAssetController extends Controller
{
    public function index(Novel $novel): View
    {
        $this->authorizeNovel($novel);

        $assets = $novel->assets3d()->latest()->get();

        return view('author.3d_assets.index', compact('novel', 'assets'));
    }

    public function store(Upload3dAssetRequest $request, Novel $novel): RedirectResponse
    {
        $file = $request->file('model_file');
        $filename = uniqid('asset_3d_') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('3d-models', $filename, 'public');
        $modelUrl = Storage::url($path);

        $novel->assets3d()->create([
            'name' => $request->input('name'),
            'asset_type' => $request->input('asset_type'),
            'model_file_url' => $modelUrl,
            'scale_factor' => $request->input('scale_factor', 1.00) ?? 1.00,
            'particle_effect' => $request->input('particle_effect', 'none') ?? 'none',
            'ambient_light_color' => $request->input('ambient_light_color', '#ffffff') ?? '#ffffff',
            'description' => $request->input('description'),
        ]);

        return redirect()->route('author.novels.3d-assets.index', $novel)
            ->with('status', 'Đã tải lên mô hình 3D thành công!');
    }

    public function destroy(Novel $novel, Novel3dAsset $asset): RedirectResponse
    {
        $this->authorizeNovel($novel);

        if ($asset->novel_id !== $novel->id) {
            abort(404);
        }

        // Delete physical file if present
        $relativePath = str_replace('/storage/', '', $asset->model_file_url);
        Storage::disk('public')->delete($relativePath);

        $asset->delete();

        return redirect()->route('author.novels.3d-assets.index', $novel)
            ->with('status', 'Đã xóa mô hình 3D.');
    }

    protected function authorizeNovel(Novel $novel): void
    {
        if ($novel->author_id !== auth()->id() && auth()->user()?->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập kho mô hình 3D của tiểu thuyết này.');
        }
    }
}
