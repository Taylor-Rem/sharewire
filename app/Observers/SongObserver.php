<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Song;
use Illuminate\Support\Facades\Storage;

class SongObserver
{
    public function deleted(Song $song): void
    {
        if ($song->file_path !== '' && Storage::disk('local')->exists($song->file_path)) {
            Storage::disk('local')->delete($song->file_path);
        }
    }
}
