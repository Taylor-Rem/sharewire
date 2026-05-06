<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LibraryEntry;
use App\Models\User;

class LibraryEntryPolicy
{
    public function view(User $user, LibraryEntry $libraryEntry): bool
    {
        return $user->id === $libraryEntry->user_id;
    }

    public function delete(User $user, LibraryEntry $libraryEntry): bool
    {
        return $user->id === $libraryEntry->user_id;
    }
}
