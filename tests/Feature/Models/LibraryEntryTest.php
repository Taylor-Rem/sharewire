<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a library entry from the factory', function (): void {
    $entry = PlaylistSong::factory()->create();

    expect($entry)->toBeInstanceOf(PlaylistSong::class)
        ->and($entry->user_id)->toBeInt()
        ->and($entry->song_id)->toBeInt()
        ->and($entry->added_at)->not->toBeNull();
});

it('casts added_at to a Carbon datetime', function (): void {
    $entry = PlaylistSong::factory()->create();

    expect($entry->added_at)->toBeInstanceOf(CarbonInterface::class);
});

it('casts position to integer', function (): void {
    $entry = PlaylistSong::factory()->create(['position' => '3']);

    expect($entry->position)->toBe(3);
});

it('belongs to a user and a song', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();
    $entry = PlaylistSong::factory()->create([
        'user_id' => $user->id,
        'song_id' => $song->id,
    ]);

    expect($entry->user)->toBeInstanceOf(User::class)
        ->and($entry->user->id)->toBe($user->id)
        ->and($entry->song)->toBeInstanceOf(Song::class)
        ->and($entry->song->id)->toBe($song->id);
});

it('rejects duplicate (user_id, song_id) pairs via the unique index', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();

    PlaylistSong::factory()->create([
        'user_id' => $user->id,
        'song_id' => $song->id,
    ]);

    expect(fn () => PlaylistSong::factory()->create([
        'user_id' => $user->id,
        'song_id' => $song->id,
    ]))->toThrow(QueryException::class);
});

it('cascades when the user is deleted', function (): void {
    $user = User::factory()->create();
    $entry = PlaylistSong::factory()->create(['user_id' => $user->id]);

    $user->delete();

    expect(PlaylistSong::find($entry->id))->toBeNull();
});

it('cascades when the song is deleted', function (): void {
    $song = Song::factory()->create();
    $entry = PlaylistSong::factory()->create(['song_id' => $song->id]);

    $song->delete();

    expect(PlaylistSong::find($entry->id))->toBeNull();
});
