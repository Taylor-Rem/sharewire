<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlaylistSong;
use App\Models\User;

class LibraryEntryPolicy
{
    public function view(User $user, PlaylistSong $libraryEntry): bool
    {
        return $user->id === $libraryEntry->user_id;
    }

    public function delete(User $user, PlaylistSong $libraryEntry): bool
    {
        return $user->id === $libraryEntry->user_id;
    }
}
