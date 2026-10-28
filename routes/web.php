<?php

use App\Http\Controllers\PrintKartuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Route::get('/print-kartu/{record}/preview', [PrintKartuController::class, 'preview'])
//     ->name('print-kartu.preview')
//     ->middleware(['auth']);


Route::get('/print-kartu/{record}/preview', [PrintKartuController::class, 'preview'])
    ->name('print-kartu.preview');

Route::get('/print-kartu/{record}/download', [PrintKartuController::class, 'download'])
    ->name('print-kartu.download');