<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaylistSongFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'playlist_id',
    'song_id',
    'added_at',
])]
class PlaylistSong extends Pivot
{
    /** @use HasFactory<PlaylistSongFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'playlist_songs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
