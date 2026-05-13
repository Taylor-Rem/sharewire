<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;

final class AddSongToPlaylist
{
    public function __invoke(User $user, Song $song, Playlist $playlist): PlaylistSong
    {
        return PlaylistSong::firstOrCreate(
            ['playlist_id' => $playlist->id, 'song_id' => $song->id],
            ['added_at' => now()],
        );
    }
}
