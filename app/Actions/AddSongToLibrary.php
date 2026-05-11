<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;

final class AddSongToLibrary
{
    public function __invoke(User $user, Song $song): PlaylistSong
    {
        return PlaylistSong::firstOrCreate(
            ['user_id' => $user->id, 'song_id' => $song->id],
            ['added_at' => now()],
        );
    }
}
