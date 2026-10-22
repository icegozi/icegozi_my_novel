<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAuthorRegistrationRequest;
use App\Models\AuthorProfile;
use App\Models\LegalConsent;
use App\Services\AuditLogService;
use App\Services\IdentityEncryptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AuthorRegistrationController extends Controller
{
    /**
     * Display terms and copyright policy documentation.
     */
    public function termsAndCopyrightPolicy(): View
    {
        return view('legal.terms_and_copyright_policy');
    }

    /**
     * Show the author e-KYC registration form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $profile = $user ? $user->authorProfile : null;

        return view('author.kyc_registration', compact('user', 'profile'));
    }

    /**
     * Handle submission of author e-KYC registration and digital copyright consent.
     */
    public function store(
        SubmitAuthorRegistrationRequest $request,
        IdentityEncryptionService $cryptoService,
        AuditLogService $auditLogService
    ): RedirectResponse {
        $user = $request->user();

        // Encrypt identity number and compute HMAC-SHA256 hash with pepper
        $cryptoData = $cryptoService->encryptAndHash($request->input('identity_number'));

        // Check if identity hash is already registered by another account
        $duplicate = AuthorProfile::where('identity_hash', $cryptoData['hash'])
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'identity_number' => 'Số CCCD/Định danh này đã được đăng ký bởi một tài khoản khác trong hệ thống.',
            ])->withInput();
        }

        // Upload identity images to Private Disk
        $frontPath = Storage::disk('local')->putFile('private/identity_documents', $request->file('identity_front_image'));
        $backPath = Storage::disk('local')->putFile('private/identity_documents', $request->file('identity_back_image'));

        // Create or update author profile
        $profile = AuthorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pen_name' => $request->input('pen_name'),
                'legal_full_name' => $request->input('legal_full_name'),
                'identity_number_encrypted' => $cryptoData['encrypted'],
                'identity_hash' => $cryptoData['hash'],
                'identity_issued_date' => $request->input('identity_issued_date'),
                'identity_front_image' => $frontPath,
                'identity_back_image' => $backPath,
                'tax_code' => $request->input('tax_code'),
                'bank_account_number' => $request->input('bank_account_number'),
                'bank_name' => $request->input('bank_name'),
                'verification_status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        // Update user's phone and role
        $user->phone = $request->input('phone');
        $user->role = 'author';
        $user->save();

        // Record immutable digital legal consents
        $ip = $request->ip() ?? '127.0.0.1';
        $userAgent = $request->userAgent() ?? 'Unknown';

        LegalConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'author_copyright_agreement',
            'policy_version' => 'v1.0-2026',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'signed_at' => now(),
        ]);

        LegalConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'terms_of_service',
            'policy_version' => 'v1.0-2026',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'signed_at' => now(),
        ]);

        // Audit Log entry
        $auditLogService->log(
            user: $user,
            action: 'submit_author_kyc',
            type: AuthorProfile::class,
            id: $profile->id,
            old: [],
            new: [
                'pen_name' => $profile->pen_name,
                'verification_status' => 'pending',
                'phone' => $user->phone,
            ]
        );

        return redirect('/tac-gia/dang-ky-dinh-danh')
            ->with('success', 'Hồ sơ định danh tác giả (e-KYC) và Cam kết bản quyền số của bạn đã được ghi nhận thành công! Ban biên tập sẽ tiến hành thẩm tra trong vòng 24 giờ.');
    }
}
