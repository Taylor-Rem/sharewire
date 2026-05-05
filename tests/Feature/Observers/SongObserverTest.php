<?php

declare(strict_types=1);

use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('deletes the audio file from disk when the song is deleted', function (): void {
    Storage::fake('local');

    $stored = Storage::disk('local')->putFile(
        'songs',
        UploadedFile::fake()->create('clip.mp3', 100, 'audio/mpeg'),
    );

    $song = Song::factory()->create(['file_path' => $stored]);

    Storage::disk('local')->assertExists($stored);

    $song->delete();

    Storage::disk('local')->assertMissing($stored);
});

it('does not error when the song file is already gone', function (): void {
    Storage::fake('local');

    $song = Song::factory()->create(['file_path' => 'songs/already-deleted.mp3']);

    expect(fn () => $song->delete())->not->toThrow(Throwable::class);
});
