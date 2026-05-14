<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Song;
use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SongData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $artist,
        public ?string $album,
        public ?string $genre,
        public ?int $duration_seconds,
        public string $mime_type,
        public UploaderData $uploader,
        public bool $is_in_my_library,
        public bool $is_uploader,
        public string $audio_url,
        /** @var int[] */
        public array $my_playlist_ids,
        public ?PivotData $pivot,
        public ?string $created_at,
    ) {}

    public static function fromModel(Song $song, ?User $viewer): self
    {
        return new self(
            id: $song->id,
            title: $song->title,
            artist: $song->artist,
            album: $song->album,
            genre: $song->genre,
            duration_seconds: $song->duration_seconds,
            mime_type: $song->mime_type,
            uploader: new UploaderData(
                id: $song->uploaded_by_user_id,
                name: $song->relationLoaded('uploader') ? $song->uploader?->name : null,
            ),
            is_in_my_library: (bool) ($song->is_in_my_library ?? false),
            is_uploader: $viewer?->id === $song->uploaded_by_user_id,
            audio_url: route('songs.audio', ['song' => $song->id]),
            my_playlist_ids: $song->relationLoaded('playlists')
                ? $song->playlists->pluck('id')->all()
                : [],
            pivot: $song->pivot
                ? new PivotData(id: (int) $song->pivot->id)
                : null,
            created_at: $song->created_at?->toIso8601String(),
        );
    }
}
