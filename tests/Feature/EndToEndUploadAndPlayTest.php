<?php

declare(strict_types=1);

use App\Models\LibraryEntry;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('walks the full upload → browse → add → stream → remove → uploader-delete flow', function (): void {
    Storage::fake('local');
    Queue::fake();

    $alice = User::factory()->create();
    $bob = User::factory()->create();

    // 1. Alice uploads a song.
    $upload = $this->actingAs($alice)->post('/songs', [
        'title' => 'The Cosmic Drift',
        'artist' => 'Stellar Crew',
        'album' => 'Void Runner',
        'genre' => 'Ambient',
        'audio' => UploadedFile::fake()->create('drift.mp3', 1500, 'audio/mpeg'),
    ]);
    $upload->assertRedirect(route('dashboard'));

    $song = Song::where('title', 'The Cosmic Drift')->firstOrFail();
    expect($song->uploaded_by_user_id)->toBe($alice->id);
    Storage::disk('local')->assertExists($song->file_path);

    // 2. Bob browses the shared library and sees Alice's upload, not yet in his library.
    $this->actingAs($bob)
        ->get(route('songs.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('songs.data', 1)
            ->where('songs.data.0.title', 'The Cosmic Drift')
            ->where('songs.data.0.is_in_my_library', false)
        );

    // 3. Bob adds it to his personal library.
    $this->actingAs($bob)
        ->from(route('songs.index'))
        ->post(route('library.store', $song))
        ->assertRedirect(route('songs.index'));

    expect(LibraryEntry::count())->toBe(1);

    // 4. The shared library now reflects "in my library".
    $this->actingAs($bob)
        ->get(route('songs.index'))
        ->assertInertia(fn ($page) => $page
            ->where('songs.data.0.is_in_my_library', true)
        );

    // 5. Bob can stream the audio (auth + policy clear, file present).
    $this->actingAs($bob)
        ->get(route('songs.audio', $song))
        ->assertOk()
        ->assertHeader('Accept-Ranges', 'bytes');

    // 6. Bob's personal library shows it.
    $this->actingAs($bob)
        ->get(route('library.index'))
        ->assertInertia(fn ($page) => $page
            ->has('songs.data', 1)
            ->where('songs.data.0.id', $song->id)
        );

    // 7. Bob removes the song from his library.
    $entry = LibraryEntry::firstOrFail();
    $this->actingAs($bob)
        ->from(route('library.index'))
        ->delete(route('library.destroy', $entry))
        ->assertRedirect(route('library.index'));

    expect(LibraryEntry::count())->toBe(0);

    // 8. Alice (the uploader) deletes the song. File goes away; row cascades clean.
    $this->actingAs($alice)
        ->from(route('songs.index'))
        ->delete(route('songs.destroy', $song))
        ->assertRedirect(route('songs.index'));

    expect(Song::find($song->id))->toBeNull();
    Storage::disk('local')->assertMissing($song->file_path);
});
