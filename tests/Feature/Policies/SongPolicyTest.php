<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use App\Policies\SongPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new SongPolicy;
    $this->uploader = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->song = Song::factory()->for($this->uploader, 'uploader')->create();
});

it('lets any authenticated user view any songs', function (): void {
    expect($this->policy->viewAny($this->otherUser))->toBeTrue();
});

it('lets any authenticated user view a single song', function (): void {
    expect($this->policy->view($this->otherUser, $this->song))->toBeTrue();
});

it('lets any authenticated user upload a song', function (): void {
    expect($this->policy->create($this->otherUser))->toBeTrue();
});

it('lets the uploader delete their song', function (): void {
    expect($this->policy->delete($this->uploader, $this->song))->toBeTrue();
});

it('forbids other users from deleting a song', function (): void {
    expect($this->policy->delete($this->otherUser, $this->song))->toBeFalse();
});

it('lets the uploader update their song', function (): void {
    expect($this->policy->update($this->uploader, $this->song))->toBeTrue();
});

it('forbids other users from updating a song', function (): void {
    expect($this->policy->update($this->otherUser, $this->song))->toBeFalse();
});

it('forbids restore and forceDelete unconditionally', function (): void {
    expect($this->policy->restore($this->uploader, $this->song))->toBeFalse()
        ->and($this->policy->forceDelete($this->uploader, $this->song))->toBeFalse();
});

it('lets the uploader play their own song without it being in their library', function (): void {
    expect($this->policy->play($this->uploader, $this->song))->toBeTrue();
});

it('lets a user play a song that is in their library', function (): void {
    PlaylistSong::factory()->create([
        'user_id' => $this->otherUser->id,
        'song_id' => $this->song->id,
    ]);

    expect($this->policy->play($this->otherUser, $this->song))->toBeTrue();
});

it('forbids playing a song that is not in the users library and they did not upload', function (): void {
    expect($this->policy->play($this->otherUser, $this->song))->toBeFalse();
});
