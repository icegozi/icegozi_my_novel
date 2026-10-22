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
    });
});
