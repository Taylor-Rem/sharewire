<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddSongToLibrary;
use App\Http\Requests\AddSongToLibraryRequest;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class LibraryEntryController extends Controller
{
    public function store(
        AddSongToLibraryRequest $request,
        Song $song,
        AddSongToLibrary $addSongToLibrary,
    ): RedirectResponse {
        $addSongToLibrary($request->user(), $song);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Added \"{$song->title}\" to your library.",
        ]);

        return back();
    }
}
