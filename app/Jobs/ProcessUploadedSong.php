<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Song;
use getID3;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUploadedSong implements ShouldQueue
{
    use Queueable;

    public function __construct(public Song $song) {}

    public function handle(): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($this->song->file_path)) {
            Log::warning('ProcessUploadedSong: file missing', [
                'song_id' => $this->song->id,
                'file_path' => $this->song->file_path,
            ]);

            return;
        }

        $absolute = $disk->path($this->song->file_path);
        $analyzer = new getID3;
        $info = $analyzer->analyze($absolute);

        $duration = isset($info['playtime_seconds'])
            ? (int) round((float) $info['playtime_seconds'])
            : null;

        if ($duration !== null && $duration > 0) {
            $this->song->forceFill(['duration_seconds' => $duration])->save();
        }
    }
}
