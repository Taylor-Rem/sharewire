<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UploadSong;
use App\Data\UploadSongData;
use App\Http\Requests\UploadSongRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SongController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Songs/Upload');
    }

    public function store(UploadSongRequest $request, UploadSong $upload): RedirectResponse
    {
        $song = $upload(UploadSongData::fromRequest($request));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Uploaded \"{$song->title}\".",
        ]);

        return to_route('dashboard');
    }
}
