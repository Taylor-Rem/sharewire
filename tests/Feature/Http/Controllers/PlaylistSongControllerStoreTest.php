<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $song = Song::factory()->create();
    $playlist = Playlist::factory()->create();

    $this->post(route('playlist_song.store', $song), ['playlist_id' => $playlist->id])
        ->assertRedirect('/login');
    expect(PlaylistSong::count())->toBe(0);
});

it('adds the song to the chosen playlist and flashes a toast', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create(['title' => 'Cosmic Drift']);
    $playlist = $user->primaryPlaylist;

    $response = $this->actingAs($user)
        ->from('/songs')
        ->post(route('playlist_song.store', $song), ['playlist_id' => $playlist->id]);

    $response->assertRedirect('/songs');
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Added "Cosmic Drift" to your library.',
    ]);

    expect(PlaylistSong::count())->toBe(1);
    $entry = PlaylistSong::first();
    expect($entry->playlist_id)->toBe($playlist->id)
        ->and($entry->song_id)->toBe($song->id);
});

it('is idempotent — second add to the same playlist does not error or duplicate', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();
    $playlist = $user->primaryPlaylist;

    $this->actingAs($user)
        ->post(route('playlist_song.store', $song), ['playlist_id' => $playlist->id])
        ->assertRedirect();
    $this->actingAs($user)
        ->post(route('playlist_song.store', $song), ['playlist_id' => $playlist->id])
        ->assertRedirect();

    expect(PlaylistSong::count())->toBe(1);
});

it('returns 404 when the song does not exist', function (): void {
    $user = User::factory()->create();
    $playlist = $user->primaryPlaylist;

    $this->actingAs($user)
        ->post('/songs/9999/playlist_song', ['playlist_id' => $playlist->id])
        ->assertNotFound();
});

it('rejects a missing playlist_id with a validation error', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();

    $this->actingAs($user)
        ->from('/songs')
        ->post(route('playlist_song.store', $song), [])
        ->assertSessionHasErrors('playlist_id');

    expect(PlaylistSong::count())->toBe(0);
});

it('rejects a playlist_id that belongs to another user', function (): void {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $song = Song::factory()->create();
    $strangersPlaylist = $stranger->primaryPlaylist;

    $this->actingAs($user)
        ->from('/songs')
        ->post(route('playlist_song.store', $song), ['playlist_id' => $strangersPlaylist->id])
        ->assertSessionHasErrors('playlist_id');

    expect(PlaylistSong::count())->toBe(0);
});

it('rejects a playlist_id that does not exist', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();

    $this->actingAs($user)
        ->from('/songs')
        ->post(route('playlist_song.store', $song), ['playlist_id' => 9999])
        ->assertSessionHasErrors('playlist_id');

    expect(PlaylistSong::count())->toBe(0);
});
