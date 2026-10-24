<?php

use App\Http\Controllers\Legal\AuthorRegistrationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Reader\CatalogController;
use App\Http\Controllers\Reader\GenreController;
use App\Http\Controllers\Reader\HomeController;

// Discovery & Catalog
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/latest', [CatalogController::class, 'latest'])->name('reader.catalog.latest');
Route::get('/popular', [CatalogController::class, 'popular'])->name('reader.catalog.popular');
Route::get('/genres', [GenreController::class, 'index'])->name('reader.genres.index');
Route::get('/genres/{slug}', [GenreController::class, 'show'])->name('reader.genres.show');
Route::get('/search', [\App\Http\Controllers\Reader\SearchController::class, 'index'])->name('reader.search');

use App\Http\Controllers\Auth\AuthController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Legal terms and copyright policy
Route::get('/dieu-khoan-va-ban-quyen', [AuthorRegistrationController::class, 'termsAndCopyrightPolicy'])
    ->name('legal.terms');
Route::get('/bao-cao-vi-pham', [\App\Http\Controllers\Legal\PublicReportController::class, 'create'])
    ->name('legal.reports.create');
Route::post('/bao-cao-vi-pham', [\App\Http\Controllers\Legal\PublicReportController::class, 'store'])
    ->name('legal.reports.store');

// Reader Novel & Chapter Routes
Route::get('/stories/{slug}', [\App\Http\Controllers\Reader\NovelViewController::class, 'show'])
    ->name('reader.stories.show');
Route::get('/truyen/{slug}', [\App\Http\Controllers\Reader\NovelViewController::class, 'show'])
    ->name('reader.novels.show');
Route::get('/truyen/{slug}/phong-trung-bay-3d', [\App\Http\Controllers\Reader\NovelViewController::class, 'exhibition'])
    ->name('reader.novels.exhibition');
Route::get('/stories/{slug}/read/{chapterNumber}', [\App\Http\Controllers\Reader\ChapterReaderController::class, 'read'])
    ->name('reader.stories.read')->where('chapterNumber', '[0-9]+(\.[0-9]+)?');
Route::get('/truyen/{slug}/chuong/{chapterNumber}', [\App\Http\Controllers\Reader\ChapterReaderController::class, 'read'])
    ->name('reader.chapters.read')->where('chapterNumber', '[0-9]+(\.[0-9]+)?');
Route::post('/api/reader/preferences', [\App\Http\Controllers\Reader\ChapterReaderController::class, 'updatePreferences'])
    ->name('reader.preferences.update');
Route::post('/api/reader/progress', [\App\Http\Controllers\Reader\ReadingHistoryController::class, 'sync'])
    ->name('reader.progress.sync');
Route::get('/api/reader/progress/{chapter}', [\App\Http\Controllers\Reader\ReadingHistoryController::class, 'show'])
    ->name('reader.progress.show');

// Authenticated Reader Area
Route::middleware('auth')->group(function () {
    Route::get('/library', [\App\Http\Controllers\Reader\UserLibraryController::class, 'library'])->name('reader.library');
    Route::get('/history', [\App\Http\Controllers\Reader\UserLibraryController::class, 'history'])->name('reader.history');
    Route::get('/bookmarks', [\App\Http\Controllers\Reader\UserLibraryController::class, 'bookmarks'])->name('reader.bookmarks');
    Route::post('/api/bookmarks/toggle', [\App\Http\Controllers\Reader\UserLibraryController::class, 'toggleBookmark'])->name('reader.bookmarks.toggle');
    Route::delete('/api/history/{id}', [\App\Http\Controllers\Reader\UserLibraryController::class, 'deleteHistory'])->name('reader.history.delete');

    Route::get('/notifications', [\App\Http\Controllers\Reader\UserProfileController::class, 'notifications'])->name('reader.notifications');
    Route::get('/profile', [\App\Http\Controllers\Reader\UserProfileController::class, 'profile'])->name('reader.profile');
    Route::post('/profile', [\App\Http\Controllers\Reader\UserProfileController::class, 'updateProfile'])->name('reader.profile.update');
    Route::get('/settings', [\App\Http\Controllers\Reader\UserProfileController::class, 'settings'])->name('reader.settings');
    Route::post('/settings', [\App\Http\Controllers\Reader\UserProfileController::class, 'updateSettings'])->name('reader.settings.update');
    Route::get('/settings/security', [\App\Http\Controllers\Reader\UserProfileController::class, 'security'])->name('reader.settings.security');
    Route::post('/settings/security', [\App\Http\Controllers\Reader\UserProfileController::class, 'updateSecurity'])->name('reader.settings.security.update');

    // Author e-KYC and digital copyright consent
    Route::get('/tac-gia/dang-ky-dinh-danh', [AuthorRegistrationController::class, 'create'])
        ->name('author.kyc');
    Route::post('/tac-gia/dang-ky-dinh-danh', [AuthorRegistrationController::class, 'store'])
        ->name('author.kyc.store');

    // Studio check route protected by EnsureAuthorVerified middleware
    Route::get('/tac-gia/studio-check', function () {
        return response()->json(['status' => 'ok']);
    })->middleware('author.verified')->name('author.studio.check');

    // Author Novel Management (CRUD)
    Route::middleware('author.verified')->group(function () {
        Route::resource('/tac-gia/truyen', \App\Http\Controllers\Author\NovelController::class)
            ->names('author.novels');

        // Zen Editor routes
        Route::get('/tac-gia/truyen/{truyen}/chuong/them-moi', [\App\Http\Controllers\Author\ChapterEditorController::class, 'create'])
            ->name('author.chapters.create');
        Route::get('/tac-gia/truyen/{truyen}/chuong/{chuong}/soan-thao', [\App\Http\Controllers\Author\ChapterEditorController::class, 'edit'])
            ->name('author.chapters.edit');

        // Autosave API endpoint
        Route::post('/api/author/chapters/autosave', [\App\Http\Controllers\Author\ChapterEditorController::class, 'autosave'])
            ->name('author.chapters.autosave');

        // Bulk chapter splitter routes
        Route::get('/tac-gia/truyen/{novel}/tach-chuong', [\App\Http\Controllers\Author\BulkChapterController::class, 'create'])
            ->name('author.novels.bulk-chapters.create');
        Route::post('/tac-gia/truyen/{novel}/tach-chuong/xem-truoc', [\App\Http\Controllers\Author\BulkChapterController::class, 'preview'])
            ->name('author.novels.bulk-chapters.preview');
        Route::post('/tac-gia/truyen/{novel}/tach-chuong/luu', [\App\Http\Controllers\Author\BulkChapterController::class, 'store'])
            ->name('author.novels.bulk-chapters.store');

        // 3D Asset Gallery & Upload
        Route::get('/tac-gia/truyen/{novel}/kho-3d', [\App\Http\Controllers\Author\Novel3dAssetController::class, 'index'])
            ->name('author.novels.3d-assets.index');
        Route::post('/tac-gia/truyen/{novel}/kho-3d', [\App\Http\Controllers\Author\Novel3dAssetController::class, 'store'])
            ->name('author.novels.3d-assets.store');
        Route::delete('/tac-gia/truyen/{novel}/kho-3d/{asset}', [\App\Http\Controllers\Author\Novel3dAssetController::class, 'destroy'])
            ->name('author.novels.3d-assets.destroy');

        // Chapter 3D Projections Linker API
        Route::get('/tac-gia/chuong/{chapter}/hieu-ung-3d', [\App\Http\Controllers\Author\Chapter3dProjectionController::class, 'index'])
            ->name('author.chapters.projections.index');
        Route::post('/tac-gia/chuong/{chapter}/hieu-ung-3d', [\App\Http\Controllers\Author\Chapter3dProjectionController::class, 'store'])
            ->name('author.chapters.projections.store');
        Route::delete('/tac-gia/chuong/{chapter}/hieu-ung-3d/{projection}', [\App\Http\Controllers\Author\Chapter3dProjectionController::class, 'destroy'])
            ->name('author.chapters.projections.destroy');
    });

    // Author Counter-Notice routes
    Route::get('/tac-gia/phan-doi-khieu-nai/{report}', [\App\Http\Controllers\Author\CounterNoticeController::class, 'create'])
        ->name('author.counter-notice.create');
    Route::post('/tac-gia/phan-doi-khieu-nai/{report}', [\App\Http\Controllers\Author\CounterNoticeController::class, 'store'])
        ->name('author.counter-notice.store');

    // Admin Dashboard Routes
    Route::middleware('admin.mod')->group(function () {
        Route::get('/admin', function () {
            return redirect()->route('admin.dashboard');
        })->name('admin.root');
        Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
    });

    // Admin & Moderator Content Moderation Routes
    Route::middleware('admin.mod')->prefix('/admin/kiem-duyet')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'queue'])
            ->name('admin.moderation.queue');
        Route::get('/chuong/{chapter}', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'reviewChapter'])
            ->name('admin.moderation.chapters.review');
        Route::post('/chuong/{chapter}/phe-duyet', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'approveChapter'])
            ->name('admin.moderation.chapters.approve');
        Route::post('/chuong/{chapter}/tu-choi', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'rejectChapter'])
            ->name('admin.moderation.chapters.reject');
        Route::post('/truyen/{novel}/phe-duyet', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'approveNovel'])
            ->name('admin.moderation.novels.approve');
        Route::post('/truyen/{novel}/tu-choi', [\App\Http\Controllers\Admin\ModerationQueueController::class, 'rejectNovel'])
            ->name('admin.moderation.novels.reject');
    });

    // Admin Copyright Management Routes (ND 17/2023)
    Route::middleware('admin.mod')->prefix('/admin/ban-quyen')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CopyrightNoticeManagementController::class, 'index'])
            ->name('admin.copyright.index');
        Route::get('/{report}', [\App\Http\Controllers\Admin\CopyrightNoticeManagementController::class, 'show'])
            ->name('admin.copyright.show');
        Route::post('/{report}/tam-an', [\App\Http\Controllers\Admin\CopyrightNoticeManagementController::class, 'temporaryTakedown'])
            ->name('admin.copyright.takedown');
        Route::post('/{report}/khoi-phuc', [\App\Http\Controllers\Admin\CopyrightNoticeManagementController::class, 'restore'])
            ->name('admin.copyright.restore');
        Route::post('/{report}/bac-don', [\App\Http\Controllers\Admin\CopyrightNoticeManagementController::class, 'dismiss'])
            ->name('admin.copyright.dismiss');
    });

    // Admin Author e-KYC Review Routes (ND 13/2023 & ND 147/2024)
    Route::middleware('admin.mod')->prefix('/admin/dinh-danh-tac-gia')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuthorKycReviewController::class, 'index'])
            ->name('admin.kyc.index');
        Route::get('/{profile}', [\App\Http\Controllers\Admin\AuthorKycReviewController::class, 'show'])
            ->name('admin.kyc.show');
        Route::get('/{profile}/tai-lieu/{side}', [\App\Http\Controllers\Admin\AuthorKycReviewController::class, 'document'])
            ->name('admin.kyc.document');
        Route::post('/{profile}/duyet', [\App\Http\Controllers\Admin\AuthorKycReviewController::class, 'approve'])
            ->name('admin.kyc.approve');
        Route::post('/{profile}/tu-choi', [\App\Http\Controllers\Admin\AuthorKycReviewController::class, 'reject'])
            ->name('admin.kyc.reject');
    });

    // Admin Author Management Desk (BRIEF-6 Module B)
    Route::middleware('admin.mod')->prefix('/admin/tac-gia')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuthorManagementController::class, 'index'])
            ->name('admin.authors.index');
        Route::get('/{author}', [\App\Http\Controllers\Admin\AuthorManagementController::class, 'show'])
            ->name('admin.authors.show');
        Route::get('/{author}/chinh-sua', [\App\Http\Controllers\Admin\AuthorManagementController::class, 'edit'])
            ->name('admin.authors.edit');
        Route::put('/{author}', [\App\Http\Controllers\Admin\AuthorManagementController::class, 'update'])
            ->name('admin.authors.update');
        Route::post('/{author}/trang-thai', [\App\Http\Controllers\Admin\AuthorManagementController::class, 'changeStatus'])
            ->name('admin.authors.status');
    });

    // Admin Novel Management (BRIEF-6 Module C)
    Route::middleware('admin.mod')->prefix('/admin/tac-pham')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NovelManagementController::class, 'index'])
            ->name('admin.novels.index');
        Route::get('/{novel}/chinh-sua', [\App\Http\Controllers\Admin\NovelManagementController::class, 'edit'])
            ->name('admin.novels.edit');
        Route::put('/{novel}', [\App\Http\Controllers\Admin\NovelManagementController::class, 'update'])
            ->name('admin.novels.update');
        Route::delete('/{novel}', [\App\Http\Controllers\Admin\NovelManagementController::class, 'destroy'])
            ->name('admin.novels.destroy');
        Route::get('/{novel}/chuong', [\App\Http\Controllers\Admin\ChapterManagementController::class, 'index'])
            ->name('admin.novels.chapters.index');
    });

    // Admin Chapter Management (BRIEF-6 Module C)
    Route::middleware('admin.mod')->prefix('/admin/chuong')->group(function () {
        Route::get('/{chapter}/chinh-sua', [\App\Http\Controllers\Admin\ChapterManagementController::class, 'edit'])
            ->name('admin.chapters.edit');
        Route::put('/{chapter}', [\App\Http\Controllers\Admin\ChapterManagementController::class, 'update'])
            ->name('admin.chapters.update');
        Route::get('/{chapter}/lich-su', [\App\Http\Controllers\Admin\ChapterVersionController::class, 'history'])
            ->name('admin.chapters.history');
        Route::post('/{chapter}/khoi-phuc/{version}', [\App\Http\Controllers\Admin\ChapterVersionController::class, 'restore'])
            ->name('admin.chapters.restore');
    });

    // Admin Bulk Import Wizard (BRIEF-6 Module D)
    Route::middleware('admin.mod')->prefix('/admin/du-lieu')->group(function () {
        Route::get('/nhap-du-lieu', [\App\Http\Controllers\Admin\ImportWizardController::class, 'index'])
            ->name('admin.import.index');
        Route::get('/mau-import/{type}', [\App\Http\Controllers\Admin\ImportWizardController::class, 'template'])
            ->name('admin.import.template');
        Route::post('/tai-len', [\App\Http\Controllers\Admin\ImportWizardController::class, 'upload'])
            ->name('admin.import.upload');
        Route::get('/xem-truoc/{job}', [\App\Http\Controllers\Admin\ImportWizardController::class, 'preview'])
            ->name('admin.import.preview');
        Route::get('/bao-cao-loi/{job}', [\App\Http\Controllers\Admin\ImportWizardController::class, 'errors'])
            ->name('admin.import.errors');
        Route::post('/xac-nhan-nap/{job}', [\App\Http\Controllers\Admin\ImportWizardController::class, 'commit'])
            ->name('admin.import.commit');
    });

    // Admin Audit Trail Explorer (ND 147/2024 Cybersecurity Compliance)
    Route::middleware('admin.mod')->prefix('/admin/nhat-ky-an-ninh')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuditLogExplorerController::class, 'index'])
            ->name('admin.audit-logs.index');
        Route::get('/xuat-du-lieu', [\App\Http\Controllers\Admin\AuditLogExplorerController::class, 'export'])
            ->name('admin.audit-logs.export');
    });
});

