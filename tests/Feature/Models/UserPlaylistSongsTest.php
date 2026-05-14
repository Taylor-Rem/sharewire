<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns every PlaylistSong row across all of the users playlists', function (): void {
    $user = User::factory()->create();
    $second = Playlist::factory()->for($user)->create();

    $song1 = Song::factory()->create();
    $song2 = Song::factory()->create();
    $song3 = Song::factory()->create();

    PlaylistSong::factory()->create(['playlist_id' => $user->primaryPlaylist->id, 'song_id' => $song1->id]);
    PlaylistSong::factory()->create(['playlist_id' => $user->primaryPlaylist->id, 'song_id' => $song2->id]);
    PlaylistSong::factory()->create(['playlist_id' => $second->id, 'song_id' => $song3->id]);

    expect($user->playlistSongs()->count())->toBe(3);
});

it('does not include other users playlist songs', function (): void {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $song = Song::factory()->create();

    PlaylistSong::factory()->create([
        'playlist_id' => $stranger->primaryPlaylist->id,
        'song_id' => $song->id,
    ]);

    expect($user->playlistSongs()->count())->toBe(0)
        ->and($stranger->playlistSongs()->count())->toBe(1);
});

it('lets you eager-load the underlying song models', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create(['title' => 'Reverb Theory']);

    PlaylistSong::factory()->create([
        'playlist_id' => $user->primaryPlaylist->id,
        'song_id' => $song->id,
    ]);

    $entries = $user->playlistSongs()->with('song')->get();

    expect($entries)->toHaveCount(1)
        ->and($entries->first()->song->title)->toBe('Reverb Theory');
});
