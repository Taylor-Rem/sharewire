<?php

declare(strict_types=1);

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $entry = PlaylistSong::factory()->create();

    $this->delete(route('library.destroy', $entry))->assertRedirect('/login');
    expect(PlaylistSong::find($entry->id))->not->toBeNull();
});

it('lets the owner remove a song from their library and flashes a toast', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create(['title' => 'Cosmic Drift']);
    $entry = PlaylistSong::factory()->create(['user_id' => $user->id, 'song_id' => $song->id]);

    $response = $this->actingAs($user)
        ->from(route('library.index'))
        ->delete(route('library.destroy', $entry));

    $response->assertRedirect(route('library.index'));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Removed "Cosmic Drift" from your library.',
    ]);

    expect(PlaylistSong::find($entry->id))->toBeNull();
});

it('forbids strangers from removing someone elses entry', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $entry = PlaylistSong::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($stranger)
        ->delete(route('library.destroy', $entry))
        ->assertForbidden();

    expect(PlaylistSong::find($entry->id))->not->toBeNull();
});

it('does not affect other entries belonging to the same user', function (): void {
    $user = User::factory()->create();
    $entry = PlaylistSong::factory()->create(['user_id' => $user->id]);
    $other = PlaylistSong::factory()->for($user, 'user')->create();

    $this->actingAs($user)->delete(route('library.destroy', $entry));

    expect(PlaylistSong::find($entry->id))->toBeNull()
        ->and(PlaylistSong::find($other->id))->not->toBeNull();
});
