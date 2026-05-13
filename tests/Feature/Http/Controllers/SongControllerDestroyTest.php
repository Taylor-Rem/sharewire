<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('redirects guests to the login screen', function (): void {
    $song = Song::factory()->create();

    $this->delete(route('songs.destroy', $song))->assertRedirect('/login');
    expect(Song::find($song->id))->not->toBeNull();
});

it('lets the uploader delete their song and removes the audio file from disk', function (): void {
    $uploader = User::factory()->create();
    $stored = Storage::disk('local')->putFile('songs', UploadedFile::fake()->create('clip.mp3', 200, 'audio/mpeg'));
    $song = Song::factory()->for($uploader, 'uploader')->create(['file_path' => $stored]);

    $response = $this->actingAs($uploader)
        ->from(route('songs.index'))
        ->delete(route('songs.destroy', $song));

    $response->assertRedirect(route('songs.index'));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => "Deleted \"{$song->title}\".",
    ]);

    expect(Song::find($song->id))->toBeNull();
    Storage::disk('local')->assertMissing($stored);
});

it('cascades playlist_songs when the song is deleted', function (): void {
    $uploader = User::factory()->create();
    $listener = User::factory()->create();
    $song = Song::factory()->for($uploader, 'uploader')->create();
    $entry = PlaylistSong::factory()->create([
        'playlist_id' => $listener->primaryPlaylist->id,
        'song_id' => $song->id,
    ]);

    $this->actingAs($uploader)->delete(route('songs.destroy', $song));

    expect(Song::find($song->id))->toBeNull()
        ->and(PlaylistSong::find($entry->id))->toBeNull();
});

it('forbids non-uploaders from deleting a song', function (): void {
    $uploader = User::factory()->create();
    $stranger = User::factory()->create();
    $song = Song::factory()->for($uploader, 'uploader')->create();

    $this->actingAs($stranger)
        ->delete(route('songs.destroy', $song))
        ->assertForbidden();

    expect(Song::find($song->id))->not->toBeNull();
});
