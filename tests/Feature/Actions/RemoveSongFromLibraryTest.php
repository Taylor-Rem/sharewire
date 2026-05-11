<?php

declare(strict_types=1);

use App\Actions\RemoveSongFromLibrary;
use App\Models\PlaylistSong;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes the library entry', function (): void {
    $entry = PlaylistSong::factory()->create();

    (new RemoveSongFromLibrary)($entry);

    expect(PlaylistSong::find($entry->id))->toBeNull();
});

it('does not affect other entries belonging to the same user', function (): void {
    $entry = PlaylistSong::factory()->create();
    $other = PlaylistSong::factory()->for($entry->user, 'user')->create();

    (new RemoveSongFromLibrary)($entry);

    expect(PlaylistSong::find($entry->id))->toBeNull()
        ->and(PlaylistSong::find($other->id))->not->toBeNull();
});
