<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $entry = PlaylistSong::factory()->create();

    $this->delete(route('playlist_song.destroy', $entry))->assertRedirect('/login');
    expect(PlaylistSong::find($entry->id))->not->toBeNull();
});

it('lets the owner remove a song from their primary playlist and flashes a toast', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create(['title' => 'Cosmic Drift']);
    $entry = PlaylistSong::factory()->create([
        'playlist_id' => $user->primaryPlaylist->id,
        'song_id' => $song->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('playlists.show', $entry->playlist_id))
        ->delete(route('playlist_song.destroy', $entry));

    $response->assertRedirect(route('playlists.show', $entry->playlist_id));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Removed "Cosmic Drift" from your library.',
    ]);

    expect(PlaylistSong::find($entry->id))->toBeNull();
});

it('forbids strangers from removing a song from someone elses playlist', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $entry = PlaylistSong::factory()->create([
        'playlist_id' => $owner->primaryPlaylist->id,
    ]);

    $this->actingAs($stranger)
        ->delete(route('playlist_song.destroy', $entry))
        ->assertForbidden();

    expect(PlaylistSong::find($entry->id))->not->toBeNull();
});

it('does not affect other songs in the same playlist', function (): void {
    $user = User::factory()->create();
    $entry = PlaylistSong::factory()->create([
        'playlist_id' => $user->primaryPlaylist->id,
    ]);
    $other = PlaylistSong::factory()->create([
        'playlist_id' => $user->primaryPlaylist->id,
    ]);

    $this->actingAs($user)->delete(route('playlist_song.destroy', $entry));

    expect(PlaylistSong::find($entry->id))->toBeNull()
        ->and(PlaylistSong::find($other->id))->not->toBeNull();
});
