<?php

declare(strict_types=1);

use App\Actions\UploadSong;
use App\Data\UploadSongData;
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

    $this->user = User::factory()->create();
    $this->action = new UploadSong;
});

function uploadData(User $user): UploadSongData
{
    return new UploadSongData(
        title: 'Test Title',
        artist: 'Test Artist',
        album: 'Test Album',
        genre: 'Rock',
        audio: UploadedFile::fake()->create('clip.mp3', 200, 'audio/mpeg'),
        uploadedByUserId: $user->id,
    );
}

it('stores the uploaded file on the local disk under songs/', function (): void {
    $song = ($this->action)(uploadData($this->user));

    expect($song->file_path)->toStartWith('songs/');
    Storage::disk('local')->assertExists($song->file_path);
});

it('creates a Song row with the submitted metadata', function (): void {
    $song = ($this->action)(uploadData($this->user));

    expect($song)->toBeInstanceOf(Song::class)
        ->and($song->title)->toBe('Test Title')
        ->and($song->artist)->toBe('Test Artist')
        ->and($song->album)->toBe('Test Album')
        ->and($song->genre)->toBe('Rock')
        ->and($song->mime_type)->toBe('audio/mpeg')
        ->and($song->duration_seconds)->toBeNull();
});

it('attributes the upload to the actor', function (): void {
    $song = ($this->action)(uploadData($this->user));

    expect($song->uploaded_by_user_id)->toBe($this->user->id);
});

it('dispatches the ProcessUploadedSong job for the new song', function (): void {
    $song = ($this->action)(uploadData($this->user));

    Queue::assertPushed(
        ProcessUploadedSong::class,
        fn (ProcessUploadedSong $job) => $job->song->is($song),
    );
});
