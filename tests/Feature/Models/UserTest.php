<?php

declare(strict_types=1);

use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes uploaded songs via the uploadedSongs relationship', function (): void {
    $user = User::factory()->create();
    Song::factory()->count(3)->for($user, 'uploader')->create();
    Song::factory()->create();

    expect($user->uploadedSongs)->toHaveCount(3);
});

it('exposes the personal library via the library relationship', function (): void {
    $user = User::factory()->create();
    $songA = Song::factory()->create();
    $songB = Song::factory()->create();
    Song::factory()->create();

    LibraryEntry::factory()->create(['user_id' => $user->id, 'song_id' => $songA->id]);
    LibraryEntry::factory()->create(['user_id' => $user->id, 'song_id' => $songB->id]);

    expect($user->library)->toHaveCount(2)
        ->and($user->library->pluck('id')->all())
        ->toEqualCanonicalizing([$songA->id, $songB->id]);
});
