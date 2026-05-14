<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Playlist;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PlaylistData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $is_primary,
        public ?int $playlist_songs_count = null,
    ) {}

    public static function fromModel(Playlist $playlist): self
    {
        return new self(
            id: $playlist->id,
            name: $playlist->name,
            is_primary: (bool) $playlist->is_primary,
            playlist_songs_count: $playlist->playlist_songs_count ?? null,
        );
    }
}
