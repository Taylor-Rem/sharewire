<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaylistSong;

final class RemoveSongFromLibrary
{
    public function __invoke(PlaylistSong $entry): void
    {
        $entry->delete();
    }
}
