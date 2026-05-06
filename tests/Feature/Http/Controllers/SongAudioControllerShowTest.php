<?php

declare(strict_types=1);

use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->disk = Storage::disk('local');
    $this->disk->put('songs/sample.mp3', random_bytes(2048));
});

it('redirects guests to the login screen', function (): void {
    $song = Song::factory()->create(['file_path' => 'songs/sample.mp3']);

    $this->get(route('songs.audio', $song))->assertRedirect('/login');
});

it('lets the uploader stream their own song', function (): void {
    $uploader = User::factory()->create();
    $song = Song::factory()->for($uploader, 'uploader')->create(['file_path' => 'songs/sample.mp3']);

    $response = $this->actingAs($uploader)->get(route('songs.audio', $song));

    $response->assertOk()
        ->assertHeader('Content-Type', $song->mime_type)
        ->assertHeader('Accept-Ranges', 'bytes');
});

it('lets a user with the song in their library stream it', function (): void {
    $listener = User::factory()->create();
    $song = Song::factory()->create(['file_path' => 'songs/sample.mp3']);
    LibraryEntry::factory()->create(['user_id' => $listener->id, 'song_id' => $song->id]);

    $this->actingAs($listener)
        ->get(route('songs.audio', $song))
        ->assertOk();
});

it('forbids a stranger who has neither uploaded nor added the song', function (): void {
    $stranger = User::factory()->create();
    $song = Song::factory()->create(['file_path' => 'songs/sample.mp3']);

    $this->actingAs($stranger)
        ->get(route('songs.audio', $song))
        ->assertForbidden();
});

it('returns 404 when the audio file is missing from disk', function (): void {
    $uploader = User::factory()->create();
    $song = Song::factory()->for($uploader, 'uploader')->create([
        'file_path' => 'songs/missing.mp3',
    ]);

    $this->actingAs($uploader)
        ->get(route('songs.audio', $song))
        ->assertNotFound();
});

it('responds with HTTP 206 partial content to a Range request', function (): void {
    $uploader = User::factory()->create();
    $song = Song::factory()->for($uploader, 'uploader')->create(['file_path' => 'songs/sample.mp3']);

    $response = $this->actingAs($uploader)
        ->withHeaders(['Range' => 'bytes=0-127'])
        ->get(route('songs.audio', $song));

    $response->assertStatus(206);
    $response->assertHeader('Content-Range');
});
