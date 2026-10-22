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
});
