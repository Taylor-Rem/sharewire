<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddSongToLibrary;
use App\Actions\RemoveSongFromLibrary;
use App\Http\Requests\AddSongToLibraryRequest;
use App\Http\Resources\SongResource;
use App\Models\LibraryEntry;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $songs = $request->user()
            ->library()
            ->with('uploader:id,name')
            ->orderByPivot('added_at', 'desc')
            ->paginate(20);

        $songs->getCollection()->each(
            fn (Song $song) => $song->setAttribute('is_in_my_library', true),
        );

        return Inertia::render('Library/Index', [
            'songs' => SongResource::collection($songs),
        ]);
    }

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

    public function destroy(
        LibraryEntry $libraryEntry,
        RemoveSongFromLibrary $removeSongFromLibrary,
    ): RedirectResponse {
        $this->authorize('delete', $libraryEntry);

        $title = $libraryEntry->song?->title ?? 'song';

        $removeSongFromLibrary($libraryEntry);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Removed \"{$title}\" from your library.",
        ]);

        return back();
    }
}
