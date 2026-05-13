<?php

declare(strict_types=1);

use App\Actions\AddSongToPlaylist;
use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = new AddSongToPlaylist;
    $this->user = User::factory()->create();
    $this->song = Song::factory()->create();
    $this->primaryPlaylist = Playlist::factory()->for($this->user)->primary()->create();
    $this->secondaryPlaylist = Playlist::factory()->for($this->user)->create();
});

it('adds the song to the playlist it was given', function (): void {
    $entry = ($this->action)($this->user, $this->song, $this->secondaryPlaylist);

    expect($entry)->toBeInstanceOf(PlaylistSong::class)
        ->and($entry->playlist_id)->toBe($this->secondaryPlaylist->id)
        ->and($entry->song_id)->toBe($this->song->id)
        ->and($entry->added_at)->not->toBeNull()
        ->and(PlaylistSong::count())->toBe(1);
});

it('can add the same song to multiple playlists for the same user', function (): void {
    ($this->action)($this->user, $this->song, $this->primaryPlaylist);
    ($this->action)($this->user, $this->song, $this->secondaryPlaylist);

    expect(PlaylistSong::count())->toBe(2)
        ->and($this->primaryPlaylist->songs()->whereKey($this->song->id)->exists())->toBeTrue()
        ->and($this->secondaryPlaylist->songs()->whereKey($this->song->id)->exists())->toBeTrue();
});

it('is idempotent — adding the same song to the same playlist twice does not duplicate', function (): void {
    ($this->action)($this->user, $this->song, $this->primaryPlaylist);
    $second = ($this->action)($this->user, $this->song, $this->primaryPlaylist);

    expect(PlaylistSong::count())->toBe(1)
        ->and($second)->toBeInstanceOf(PlaylistSong::class)
        ->and($second->playlist_id)->toBe($this->primaryPlaylist->id);
});

it('lets two different users hold the same song in their own playlists', function (): void {
    $otherUser = User::factory()->create();
    $otherPlaylist = Playlist::factory()->for($otherUser)->primary()->create();

    ($this->action)($this->user, $this->song, $this->primaryPlaylist);
    ($this->action)($otherUser, $this->song, $otherPlaylist);

    expect(PlaylistSong::count())->toBe(2);
});
