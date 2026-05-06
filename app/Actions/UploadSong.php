<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UploadSongData;
use App\Jobs\ProcessUploadedSong;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;

final class UploadSong
{
    public function __invoke(UploadSongData $data): Song
    {
        $path = Storage::disk('local')->putFile('songs', $data->audio);

        $song = Song::create([
            'title' => $data->title,
            'artist' => $data->artist,
            'album' => $data->album,
            'genre' => $data->genre,
            'duration_seconds' => null,
            'file_path' => $path,
            'mime_type' => $data->audio->getMimeType() ?? 'audio/mpeg',
            'uploaded_by_user_id' => $data->uploadedByUserId,
        ]);

        ProcessUploadedSong::dispatch($song);

        return $song;
    }
}
