<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Song;
use App\Models\User;

class SongPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Song $song): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Song $song): bool
    {
        return $user->id === $song->uploaded_by_user_id;
    }

    public function delete(User $user, Song $song): bool
    {
        return $user->id === $song->uploaded_by_user_id;
    }

    /**
     * Whether the user can stream the song's audio. The uploader can always
     * play their own upload; everyone else needs the song in their library.
     */
    public function play(User $user, Song $song): bool
    {
        if ($user->id === $song->uploaded_by_user_id) {
            return true;
        }

        return $user->primaryPlaylist
            ?->songs()
            ->whereKey($song->id)
            ->exists()
            ?? false;
    }

    public function restore(User $user, Song $song): bool
    {
        return false;
    }

    public function forceDelete(User $user, Song $song): bool
    {
        return false;
    }
}
