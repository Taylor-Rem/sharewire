<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaylistRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Http\Resources\SongResource;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Playlists/Index', [
            'playlists' => $request->user()
                ->playlists()
                ->withCount('playlistSongs')
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StorePlaylistRequest $request): RedirectResponse
    {
        $playlist = $request->user()->playlists()->create([
            'name' => $request->validated('name'),
            'is_primary' => false,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Created playlist \"{$playlist->name}\".",
        ]);

        return back();
    }

    public function show(Playlist $playlist): Response
    {
        $this->authorize('view', $playlist);

        $songs = $playlist->songs()
            ->with('uploader:id,name')
            ->orderByPivot('added_at', 'desc')
            ->paginate(20);

        $songs->getCollection()->each(
            fn (Song $song) => $song->setAttribute('is_in_my_library', true),
        );

        return Inertia::render('Playlists/Show', [
            'playlist' => $playlist->only(['id', 'name', 'is_primary']),
            'songs' => SongResource::collection($songs),
        ]);
    }

    public function update(UpdatePlaylistRequest $request, Playlist $playlist): RedirectResponse
    {
        //
    }

    public function destroy(Playlist $playlist): RedirectResponse
    {
        //
    }
}
