<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\User;
use App\Policies\PlaylistSongPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new PlaylistSongPolicy;
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->playlistSong = PlaylistSong::factory()->create([
        'playlist_id' => $this->owner->primaryPlaylist->id,
    ]);
});

it('lets the owner view a song in their playlist', function (): void {
    expect($this->policy->view($this->owner, $this->playlistSong))->toBeTrue();
});

it('forbids strangers from viewing a song in someone elses playlist', function (): void {
    expect($this->policy->view($this->stranger, $this->playlistSong))->toBeFalse();
});

it('lets the owner remove a song from their playlist', function (): void {
    expect($this->policy->delete($this->owner, $this->playlistSong))->toBeTrue();
});

it('forbids strangers from removing a song from someone elses playlist', function (): void {
    expect($this->policy->delete($this->stranger, $this->playlistSong))->toBeFalse();
});
