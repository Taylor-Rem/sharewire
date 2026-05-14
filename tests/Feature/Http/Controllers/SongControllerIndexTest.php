<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the login screen', function (): void {
    $this->get('/songs')->assertRedirect('/login');
});

it('renders the index page for authenticated users with all songs newest first', function (): void {
    $user = User::factory()->create();
    $oldest = Song::factory()->create(['title' => 'Oldest', 'created_at' => now()->subHour()]);
    $newest = Song::factory()->create(['title' => 'Newest', 'created_at' => now()]);

    $response = $this->actingAs($user)->get('/songs');

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('Songs/Index')
            ->has('songs.data', 2)
            ->where('songs.data.0.id', $newest->id)
            ->where('songs.data.1.id', $oldest->id)
            ->where('filters.q', '')
    );
});

it('filters by title via the q parameter', function (): void {
    $user = User::factory()->create();
    $hit = Song::factory()->create(['title' => 'Mountain Reverie']);
    Song::factory()->create(['title' => 'Different Song']);

    $response = $this->actingAs($user)->get('/songs?q=mountain');

    $response->assertInertia(
        fn ($page) => $page
            ->has('songs.data', 1)
            ->where('songs.data.0.id', $hit->id)
            ->where('filters.q', 'mountain')
    );
});

it('filters by artist via the q parameter', function (): void {
    $user = User::factory()->create();
    $hit = Song::factory()->create(['artist' => 'The Pinecones']);
    Song::factory()->create(['artist' => 'Sea Shanty Crew']);

    $response = $this->actingAs($user)->get('/songs?q=pinecones');

    $response->assertInertia(fn ($page) => $page->where('songs.data.0.id', $hit->id)->has('songs.data', 1));
});

it('filters by album and genre via the q parameter', function (): void {
    $user = User::factory()->create();
    $byAlbum = Song::factory()->create([
        'album' => 'Twilight Sessions',
        'genre' => 'Indie',
    ]);
    $byGenre = Song::factory()->create([
        'album' => 'Other',
        'genre' => 'Twilight Jazz',
    ]);
    Song::factory()->create(['album' => 'Other', 'genre' => 'Indie']);

    $response = $this->actingAs($user)->get('/songs?q=twilight');

    $response->assertInertia(fn ($page) => $page
        ->has('songs.data', 2)
        ->where(
            'songs.data',
            fn ($rows) => collect($rows)->pluck('id')
                ->sort()
                ->values()
                ->all() === collect([$byAlbum->id, $byGenre->id])->sort()->values()->all(),
        )
    );
});

it('reflects whether each song is in the current users primary playlist', function (): void {
    $user = User::factory()->create();
    $inPlaylist = Song::factory()->create();
    $notInPlaylist = Song::factory()->create();

    PlaylistSong::factory()->create([
        'playlist_id' => $user->primaryPlaylist->id,
        'song_id' => $inPlaylist->id,
    ]);

    $response = $this->actingAs($user)->get('/songs');

    $response->assertInertia(fn ($page) => $page
        ->has('songs.data', 2)
        ->where(
            'songs.data',
            fn ($rows) => collect($rows)->firstWhere('id', $inPlaylist->id)['is_in_my_library'] === true
                && collect($rows)->firstWhere('id', $notInPlaylist->id)['is_in_my_library'] === false,
        )
    );
});

it('paginates results 20 per page', function (): void {
    $user = User::factory()->create();
    Song::factory()->count(25)->create();

    $response = $this->actingAs($user)->get('/songs');

    $response->assertInertia(fn ($page) => $page
        ->has('songs.data', 20)
        ->where('songs.meta.total', 25)
        ->where('songs.meta.last_page', 2)
    );
});

it('passes the users own playlists, primary first, ordered by name', function (): void {
    $user = User::factory()->create();
    Playlist::factory()->for($user)->create(['name' => 'Workout', 'is_primary' => false]);
    Playlist::factory()->for($user)->create(['name' => 'Chill', 'is_primary' => false]);

    // A stranger's playlist must not leak into the response.
    $stranger = User::factory()->create();
    Playlist::factory()->for($stranger)->create(['name' => 'Strangers Playlist']);

    $response = $this->actingAs($user)->get('/songs');

    $response->assertInertia(fn ($page) => $page
        ->has('playlists', 3)
        ->where('playlists.0.is_primary', true)
        ->where('playlists.1.name', 'Chill')
        ->where('playlists.2.name', 'Workout')
    );
});

it('exposes my_playlist_ids reflecting which of the users playlists already hold each song', function (): void {
    $user = User::factory()->create();
    $song = Song::factory()->create();
    $other = Song::factory()->create();

    $primary = $user->primaryPlaylist;
    $second = Playlist::factory()->for($user)->create();

    PlaylistSong::factory()->create(['playlist_id' => $primary->id, 'song_id' => $song->id]);
    PlaylistSong::factory()->create(['playlist_id' => $second->id, 'song_id' => $song->id]);

    $response = $this->actingAs($user)->get('/songs');

    $response->assertInertia(fn ($page) => $page
        ->where(
            'songs.data',
            fn ($rows) => collect(collect($rows)->firstWhere('id', $song->id)['my_playlist_ids'])->sort()->values()->all()
                === collect([$primary->id, $second->id])->sort()->values()->all()
                && collect($rows)->firstWhere('id', $other->id)['my_playlist_ids'] === [],
        )
    );
});

it('preserves the q filter when results span multiple pages', function (): void {
    $user = User::factory()->create();
    Song::factory()
        ->count(25)
        ->state(new Sequence(fn ($sequence) => [
            'title' => "Hit Track {$sequence->index}",
        ]))
        ->create();
    Song::factory()->create(['title' => 'Excluded Single']);

    $response = $this->actingAs($user)->get('/songs?q=Hit');

    $response->assertInertia(fn ($page) => $page
        ->has('songs.data', 20)
        ->where('songs.meta.total', 25)
    );
});
