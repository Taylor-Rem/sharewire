<?php

declare(strict_types=1);

use App\Actions\AddSongToLibrary;
use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = new AddSongToLibrary;
    $this->user = User::factory()->create();
    $this->song = Song::factory()->create();
});

it('creates a library entry on first call', function (): void {
    $entry = ($this->action)($this->user, $this->song);

    expect($entry)->toBeInstanceOf(LibraryEntry::class)
        ->and($entry->user_id)->toBe($this->user->id)
        ->and($entry->song_id)->toBe($this->song->id)
        ->and($entry->added_at)->not->toBeNull();

    expect(LibraryEntry::count())->toBe(1);
});

it('is idempotent — calling twice does not duplicate the row', function (): void {
    ($this->action)($this->user, $this->song);
    $second = ($this->action)($this->user, $this->song);

    expect(LibraryEntry::count())->toBe(1)
        ->and($second)->toBeInstanceOf(LibraryEntry::class);
});

it('lets a different user add the same song independently', function (): void {
    $other = User::factory()->create();

    ($this->action)($this->user, $this->song);
    ($this->action)($other, $this->song);

    expect(LibraryEntry::count())->toBe(2);
});
