<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('reproduces e2e failure', function (): void {
    Storage::fake('local');
    Queue::fake();

    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $this->actingAs($alice)->post('/songs', [
        'title' => 'The Cosmic Drift',
        'artist' => 'Stellar Crew',
        'album' => 'Void Runner',
        'genre' => 'Ambient',
        'audio' => UploadedFile::fake()->create('drift.mp3', 1500, 'audio/mpeg'),
    ]);
    $song = Song::where('title', 'The Cosmic Drift')->firstOrFail();

    $this->actingAs($bob)
        ->from(route('songs.index'))
        ->post(route('playlist_song.store', $song), ['playlist_id' => $bob->primaryPlaylist->id]);

    $this->withoutExceptionHandling();
    $this->actingAs($bob)->get(route('playlists.show', $bob->primaryPlaylist->id));
});
