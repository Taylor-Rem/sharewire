<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\LibraryEntry;

final class RemoveSongFromLibrary
{
    public function __invoke(LibraryEntry $entry): void
    {
        $entry->delete();
    }
}
