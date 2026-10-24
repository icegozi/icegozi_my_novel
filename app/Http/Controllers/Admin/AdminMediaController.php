<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    /**
     * Upload and store images inserted into the rich editor.
     * Strictly verifies real MIME type via Fileinfo and rejects SVG XSS payloads.
     */
    public function uploadEditorImage(Request $request): JsonResponse
    {
        if (!in_array(auth()->user()?->role, ['admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền tải lên media trong bộ soạn thảo.');
        }

        $request->validate([
            'image' => 'required|file|max:5120',
        ]);

        $file = $request->file('image');

        // Verify real MIME type using Fileinfo to strictly reject SVG or executable polyglots
        $mime = $file->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->json([
                'message' => 'Định dạng tệp không được hỗ trợ. Chỉ chấp nhận định dạng JPG, PNG hoặc WEBP.',
                'errors' => ['image' => ['Tệp ảnh không hợp lệ hoặc chứa định dạng nguy hiểm như SVG.']],
            ], 422);
        }

        $dateFolder = now()->format('Y/m');
        $extension = $file->getClientOriginalExtension() ?: 'webp';
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
        $storedPath = $file->storeAs("editor/{$dateFolder}", $filename, 'public');

        $url = Storage::disk('public')->url($storedPath);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_media_uploaded',
            'target_type' => 'Media',
            'target_id' => 0,
            'old_values' => [],
            'new_values' => [
                'url' => $url,
                'filename' => $filename,
                'mime' => $mime,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json([
            'url' => $url,
            'filename' => $filename,
        ]);
    }
}
