<?php

declare(strict_types=1);

use App\Jobs\ProcessUploadedSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Queue::fake();
});

it('redirects guests to the login screen', function (): void {
    $response = $this->post('/songs', [
        'title' => 'X',
        'artist' => 'Y',
        'audio' => UploadedFile::fake()->create('clip.mp3', 100, 'audio/mpeg'),
    ]);

    $response->assertRedirect('/login');
    expect(Song::count())->toBe(0);
});

it('stores the song and redirects on a valid upload', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/songs', [
        'title' => 'Hello World',
        'artist' => 'Test Artist',
        'album' => 'First Album',
        'genre' => 'Rock',
        'audio' => UploadedFile::fake()->create('clip.mp3', 200, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => 'Uploaded "Hello World".',
    ]);

    expect(Song::count())->toBe(1);
    $song = Song::first();
    expect($song->title)->toBe('Hello World')
        ->and($song->artist)->toBe('Test Artist')
        ->and($song->album)->toBe('First Album')
        ->and($song->genre)->toBe('Rock')
        ->and($song->uploaded_by_user_id)->toBe($user->id);

    Storage::disk('local')->assertExists($song->file_path);
    Queue::assertPushed(ProcessUploadedSong::class);
});

it('returns 422 when required fields are missing', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/songs', [
        'title' => '',
        'artist' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'artist', 'audio']);
    expect(Song::count())->toBe(0);
});

it('rejects non-MP3 uploads', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/songs', [
        'title' => 'Hello',
        'artist' => 'Artist',
        'audio' => UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertSessionHasErrors('audio');
    expect(Song::count())->toBe(0);
});

it('rejects files larger than the 100 MB cap', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/songs', [
        'title' => 'Hello',
        'artist' => 'Artist',
        'audio' => UploadedFile::fake()->create('big.mp3', 110_000, 'audio/mpeg'),
    ]);

    $response->assertSessionHasErrors('audio');
    expect(Song::count())->toBe(0);
});

it('renders the upload page for authenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/songs/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Songs/Upload'));
});

it('redirects guests away from the upload page', function (): void {
    $this->get('/songs/create')->assertRedirect('/login');
});
