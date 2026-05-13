<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlaylistSong;
use App\Models\User;

class PlaylistSongPolicy
{
    public function view(User $user, PlaylistSong $playlistSong): bool
    {
        return $user->id === $playlistSong->playlist->user_id;
    }

    public function delete(User $user, PlaylistSong $playlistSong): bool
    {
        return $user->id === $playlistSong->playlist->user_id;
    }
}
