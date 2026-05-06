<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\UploadSongRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Typed input for the UploadSong action.
 *
 * Hand-written because spatie/laravel-data does not yet support Laravel 13.
 * When it does, this can be migrated to extend Spatie\LaravelData\Data with
 * minimal changes — the constructor signature stays the same.
 */
final readonly class UploadSongData
{
    public function __construct(
        public string $title,
        public string $artist,
        public ?string $album,
        public ?string $genre,
        public UploadedFile $audio,
        public int $uploadedByUserId,
    ) {}

    public static function fromRequest(UploadSongRequest $request): self
    {
        /** @var UploadedFile $audio */
        $audio = $request->file('audio');

        /** @var User $user */
        $user = $request->user();

        return new self(
            title: $request->string('title')->toString(),
            artist: $request->string('artist')->toString(),
            album: $request->input('album'),
            genre: $request->input('genre'),
            audio: $audio,
            uploadedByUserId: $user->id,
        );
    }
}
