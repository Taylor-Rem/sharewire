<?php

declare(strict_types=1);

use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistSongController;
use App\Http\Controllers\SongAudioController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('songs', SongController::class)
        ->only(['index', 'create', 'store', 'destroy']);
    Route::get('songs/{song}/audio', [SongAudioController::class, 'show'])
        ->name('songs.audio');

    Route::resource('playlist_song', PlaylistSongController::class)
        ->only(['index', 'destroy'])
        ->parameters(['playlist_song' => 'playlistSong']);
    Route::post('songs/{song}/playlist_song', [PlaylistSongController::class, 'store'])
        ->name('playlist_song.store');

    Route::resource('playlists', PlaylistController::class)->except(['create', 'edit']);
});

require __DIR__.'/settings.php';
