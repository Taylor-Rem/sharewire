<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a song from the factory', function (): void {
    $song = Song::factory()->create();

    expect($song)->toBeInstanceOf(Song::class)
        ->and($song->title)->toBeString()
        ->and($song->artist)->toBeString()
        ->and($song->mime_type)->toBe('audio/mpeg');
});

it('casts duration_seconds to integer', function (): void {
    $song = Song::factory()->create(['duration_seconds' => '180']);

    expect($song->duration_seconds)->toBe(180);
});

it('belongs to the uploader', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->for($user, 'uploader')->create();

    expect($song->uploader)->toBeInstanceOf(User::class)
        ->and($song->uploader->id)->toBe($user->id);
});

it('cascades when the uploader is deleted', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->for($user, 'uploader')->create();

    $user->delete();

    expect(Song::find($song->id))->toBeNull();
});

it('exposes the users who have it in their library', function (): void {
    $song = Song::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    PlaylistSong::factory()->create(['song_id' => $song->id, 'user_id' => $userA->id]);
    PlaylistSong::factory()->create(['song_id' => $song->id, 'user_id' => $userB->id]);

    expect($song->inLibrariesOf)->toHaveCount(2)
        ->and($song->inLibrariesOf->pluck('id')->all())
        ->toEqualCanonicalizing([$userA->id, $userB->id]);
});
