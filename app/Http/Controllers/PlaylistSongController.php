<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddSongToPlaylist;
use App\Actions\RemoveSongFromLibrary;
use App\Http\Requests\AddSongToLibraryRequest;
use App\Http\Resources\SongResource;
use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistSongController extends Controller
{
    public function index(Request $request): Response
    {
        $songs = $request->user()
            ->primaryPlaylist
            ->songs()
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
        AddSongToPlaylist $addSongToLibrary,
    ): RedirectResponse {
        $playlist = Playlist::findOrFail($request->validated('playlist_id'));

        $addSongToLibrary($request->user(), $song, $playlist);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Added \"{$song->title}\" to your library.",
        ]);

        return back();
    }

    public function destroy(
        PlaylistSong $playlistSong,
        RemoveSongFromLibrary $removeSongFromLibrary,
    ): RedirectResponse {
        $this->authorize('delete', $playlistSong);

        $title = $playlistSong->song?->title ?? 'song';

        $removeSongFromLibrary($playlistSong);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Removed \"{$title}\" from your library.",
        ]);

        return back();
    }
}
