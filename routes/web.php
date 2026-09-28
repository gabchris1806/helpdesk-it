<?php

use App\Http\Controllers\PublicTicketController;
use Illuminate\Support\Facades\Route;

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

Route::redirect('/login', '/admin/login')->name('login');
