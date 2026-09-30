<?php

use App\Http\Controllers\PublicTicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserTicketController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'create'])->name('login');
    Route::post('/login', [UserAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/logout', [UserAuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'user.role'])->prefix('user')->name('user.')->group(function () {
    Route::get('/check-ticket', [UserTicketController::class, 'index'])->name('check-ticket');
});

Route::controller(PublicTicketController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::post('/laporan-store', 'store')->middleware('throttle:5,1')->name('laporan.store');
    Route::get('/laporan-sukses/{uuid}', 'success')->name('laporan.sukses');

    Route::prefix('laporan')->group(function () {
        Route::get('/cek', 'cek')->middleware('throttle:30,1')->name('laporan.cek');
        Route::post('/akses/{uuid}', 'authorizeAccess')->middleware('throttle:5,1')->name('laporan.authorize');
        Route::get('/chat-history', 'chatHistory')->middleware('throttle:90,1')->name('laporan.chat-history');
    });

    Route::post('/laporan-reply/{uuid}', 'reply')->middleware('throttle:12,1')->name('laporan.reply');
    Route::post('/laporan-upload-trix', 'uploadTrixImage')->middleware('throttle:12,1')->name('laporan.upload_trix');
});
