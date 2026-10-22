<?php

use App\Http\Controllers\Legal\AuthorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response('Login Page', 200);
})->name('login');

// Legal terms and copyright policy
Route::get('/dieu-khoan-va-ban-quyen', [AuthorRegistrationController::class, 'termsAndCopyrightPolicy'])
    ->name('legal.terms');

// Reader Novel & Chapter Routes
Route::get('/truyen/{slug}', [\App\Http\Controllers\Reader\NovelViewController::class, 'show'])
    ->name('reader.novels.show');
Route::get('/truyen/{slug}/phong-trung-bay-3d', [\App\Http\Controllers\Reader\NovelViewController::class, 'exhibition'])
    ->name('reader.novels.exhibition');
Route::get('/truyen/{slug}/chuong/{chapterNumber}', [\App\Http\Controllers\Reader\ChapterReaderController::class, 'read'])
    ->name('reader.chapters.read');
Route::post('/api/reader/preferences', [\App\Http\Controllers\Reader\ChapterReaderController::class, 'updatePreferences'])
    ->name('reader.preferences.update');

// Author e-KYC and digital copyright consent
Route::middleware('auth')->group(function () {
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
});
