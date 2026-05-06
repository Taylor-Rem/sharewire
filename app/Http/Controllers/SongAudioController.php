<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SongAudioController extends Controller
{
    /**
     * Stream a song's audio file. Browsers issue HTTP Range requests when
     * seeking through long tracks; Symfony's BinaryFileResponse handles
     * partial-content responses automatically when the request has a
     * Range header, so seeking works without us shipping the whole file
     * for every play.
     *
     * Today: protected by session auth + the play policy. When we move
     * behind HTTPS we can swap to signed URLs without changing the
     * frontend (the <audio src> just needs whatever URL we emit).
     */
    public function show(Request $request, Song $song): BinaryFileResponse
    {
        $this->authorize('play', $song);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($song->file_path), 404);

        return response()->file($disk->path($song->file_path), [
            'Content-Type' => $song->mime_type,
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
