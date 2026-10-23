<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AuthorKycResultMail;
use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Services\IdentityEncryptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorKycReviewController extends Controller
{
    /**
     * Display list of pending e-KYC submissions.
     */
    public function index(): View
    {
        $profiles = AuthorProfile::where('verification_status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('admin.kyc.index', compact('profiles'));
    }

    /**
     * View detailed KYC application dossier.
     */
    public function show(AuthorProfile $profile, IdentityEncryptionService $crypto): View
    {
        $decryptedIdNumber = '';
        if ($profile->identity_number_encrypted) {
            try {
                $decryptedIdNumber = $crypto->decrypt($profile->identity_number_encrypted);
            } catch (\Exception $e) {
                $decryptedIdNumber = '[Lỗi Giải Mã]';
            }
        }

        $frontSignedUrl = $profile->identity_front_image ? URL::temporarySignedRoute('admin.kyc.document', now()->addMinutes(10), [
            'profile' => $profile->id,
            'side' => 'front',
        ]) : null;

        $backSignedUrl = $profile->identity_back_image ? URL::temporarySignedRoute('admin.kyc.document', now()->addMinutes(10), [
            'profile' => $profile->id,
            'side' => 'back',
        ]) : null;

        return view('admin.kyc.show', compact('profile', 'decryptedIdNumber', 'frontSignedUrl', 'backSignedUrl'));
    }

    /**
     * Stream protected CCCD image via temporary signed URL.
     */
    public function document(Request $request, AuthorProfile $profile, string $side): mixed
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Liên kết xem tài liệu đã hết hạn (TTL 10 phút) hoặc không hợp lệ.');
        }

        $filePath = $side === 'front' ? $profile->identity_front_image : $profile->identity_back_image;

        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            abort(404, 'Tập tin tài liệu không tồn tại hoặc đã được xóa vĩnh viễn theo chính sách bảo mật.');
        }

        return Storage::disk('local')->response($filePath);
    }

    /**
     * Approve KYC application: promote user to author role and permanently delete CCCD images.
     */
    public function approve(AuthorProfile $profile): RedirectResponse
    {
        $oldStatus = $profile->verification_status;

        // Retention Policy (ND 13/2023): permanently purge physical files from disk
        if ($profile->identity_front_image) {
            Storage::disk('local')->delete($profile->identity_front_image);
        }
        if ($profile->identity_back_image) {
            Storage::disk('local')->delete($profile->identity_back_image);
        }

        $profile->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'identity_front_image' => null,
            'identity_back_image' => null,
        ]);

        $profile->user->update([
            'role' => 'author',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'author_kyc_approved',
            'target_type' => 'AuthorProfile',
            'target_id' => $profile->id,
            'old_values' => ['verification_status' => $oldStatus],
            'new_values' => [
                'verification_status' => 'verified',
                'verified_at' => now(),
                'cccd_images_purged' => true,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        if ($profile->user && $profile->user->email) {
            Mail::to($profile->user->email)->send(new AuthorKycResultMail($profile, 'verified'));
        }

        return redirect()->route('admin.kyc.index')
            ->with('success', "Hồ sơ định danh của tác giả '{$profile->pen_name}' đã được phê duyệt. Ảnh CCCD đã được xóa vĩnh viễn.");
    }

    /**
     * Reject KYC application: record reason and delete CCCD images.
     */
    public function reject(Request $request, AuthorProfile $profile): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $profile->verification_status;

        // Retention Policy: purge files on rejection as well
        if ($profile->identity_front_image) {
            Storage::disk('local')->delete($profile->identity_front_image);
        }
        if ($profile->identity_back_image) {
            Storage::disk('local')->delete($profile->identity_back_image);
        }

        $profile->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
            'identity_front_image' => null,
            'identity_back_image' => null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'author_kyc_rejected',
            'target_type' => 'AuthorProfile',
            'target_id' => $profile->id,
            'old_values' => ['verification_status' => $oldStatus],
            'new_values' => [
                'verification_status' => 'rejected',
                'reason' => $request->input('rejection_reason'),
                'cccd_images_purged' => true,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        if ($profile->user && $profile->user->email) {
            Mail::to($profile->user->email)->send(new AuthorKycResultMail($profile, 'rejected', $request->input('rejection_reason')));
        }

        return redirect()->route('admin.kyc.index')
            ->with('warning', "Hồ sơ tác giả '{$profile->pen_name}' đã bị từ chối.");
    }
}
