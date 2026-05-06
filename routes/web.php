<?php

declare(strict_types=1);

use App\Http\Controllers\LibraryEntryController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('songs', [SongController::class, 'index'])->name('songs.index');
    Route::get('songs/create', [SongController::class, 'create'])->name('songs.create');
    Route::post('songs', [SongController::class, 'store'])->name('songs.store');

    Route::post('songs/{song}/library', [LibraryEntryController::class, 'store'])
        ->name('library.store');
});

require __DIR__.'/settings.php';
