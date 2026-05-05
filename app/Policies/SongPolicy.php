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

    public function restore(User $user, Song $song): bool
    {
        return false;
    }

    public function forceDelete(User $user, Song $song): bool
    {
        return false;
    }
}
