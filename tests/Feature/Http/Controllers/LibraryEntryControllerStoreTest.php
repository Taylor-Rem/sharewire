<?php

declare(strict_types=1);

use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $song = Song::factory()->create();

    $this->post("/songs/{$song->id}/library")->assertRedirect('/login');
    expect(LibraryEntry::count())->toBe(0);
});

it('adds the song to the current users library and flashes a toast', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create(['title' => 'Cosmic Drift']);

    $response = $this->actingAs($user)
        ->from('/songs')
        ->post("/songs/{$song->id}/library");

    $response->assertRedirect('/songs');
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Added "Cosmic Drift" to your library.',
    ]);

    expect(LibraryEntry::count())->toBe(1);
    $entry = LibraryEntry::first();
    expect($entry->user_id)->toBe($user->id)
        ->and($entry->song_id)->toBe($song->id);
});

it('is idempotent — second add does not error or duplicate', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();

    $this->actingAs($user)->post("/songs/{$song->id}/library")->assertRedirect();
    $this->actingAs($user)->post("/songs/{$song->id}/library")->assertRedirect();

    expect(LibraryEntry::count())->toBe(1);
});

it('returns 404 when the song does not exist', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/songs/9999/library')->assertNotFound();
});
