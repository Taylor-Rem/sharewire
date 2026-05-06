<?php

declare(strict_types=1);

use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $this->get(route('library.index'))->assertRedirect('/login');
});

it('renders the personal library page with only the users own entries', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mySong = Song::factory()->create(['title' => 'Mine']);
    $otherSong = Song::factory()->create(['title' => 'Theirs']);

    LibraryEntry::factory()->create(['user_id' => $user->id, 'song_id' => $mySong->id]);
    LibraryEntry::factory()->create(['user_id' => $other->id, 'song_id' => $otherSong->id]);

    $response = $this->actingAs($user)->get(route('library.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Library/Index')
            ->has('songs.data', 1)
            ->where('songs.data.0.title', 'Mine')
            ->where('songs.data.0.is_in_my_library', true)
    );
});

it('returns the songs ordered by added_at desc', function (): void {
    $user = User::factory()->create();
    $first = Song::factory()->create();
    $second = Song::factory()->create();

    LibraryEntry::factory()->create([
        'user_id' => $user->id,
        'song_id' => $first->id,
        'added_at' => now()->subDay(),
    ]);
    LibraryEntry::factory()->create([
        'user_id' => $user->id,
        'song_id' => $second->id,
        'added_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('library.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('songs.data.0.id', $second->id)
        ->where('songs.data.1.id', $first->id)
    );
});

it('paginates 20 per page', function (): void {
    $user = User::factory()->create();
    $songs = Song::factory()->count(25)->create();
    foreach ($songs as $song) {
        LibraryEntry::factory()->create(['user_id' => $user->id, 'song_id' => $song->id]);
    }

    $response = $this->actingAs($user)->get(route('library.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('songs.data', 20)
        ->where('songs.meta.total', 25)
    );
});

it('eager-loads the uploader to avoid N+1', function (): void {
    $user = User::factory()->create();
    $uploader = User::factory()->create(['name' => 'Some Uploader']);
    $song = Song::factory()->for($uploader, 'uploader')->create();
    LibraryEntry::factory()->create(['user_id' => $user->id, 'song_id' => $song->id]);

    $response = $this->actingAs($user)->get(route('library.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('songs.data.0.uploader.name', 'Some Uploader')
    );
});
