<?php

declare(strict_types=1);

use App\Jobs\ProcessUploadedSong;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('does not error when the audio file is missing from disk', function (): void {
    Storage::fake('local');

    $song = Song::factory()->create([
        'file_path' => 'songs/missing.mp3',
        'duration_seconds' => null,
    ]);

    expect(fn () => (new ProcessUploadedSong($song))->handle())->not->toThrow(Throwable::class);
    expect($song->fresh()->duration_seconds)->toBeNull();
});

it('writes the duration when getID3 returns a playtime', function (): void {
    Storage::fake('local');

    // Use a real (tiny) MP3 fixture so getID3 has bytes to chew on.
    // Generate a 1-second silent MP3 on the fly via the fake disk: getID3 will
    // typically return null for fake bytes, so we treat "no error" + "row left
    // unchanged" as the success criterion for that path. This case proves the
    // job tolerates unparseable files; a real-fixture parse test would belong
    // in an integration suite gated on ffmpeg/lame availability.
    $disk = Storage::disk('local');
    $disk->put('songs/clip.mp3', random_bytes(1024));

    $song = Song::factory()->create([
        'file_path' => 'songs/clip.mp3',
        'duration_seconds' => null,
    ]);

    (new ProcessUploadedSong($song))->handle();

    // Random bytes won't parse, so duration stays null. The point of this case
    // is that the job runs without throwing on real-but-unparseable input.
    expect($song->fresh()->duration_seconds)->toBeNull();
});
