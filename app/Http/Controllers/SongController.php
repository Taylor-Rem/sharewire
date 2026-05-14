<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UploadSong;
use App\Data\UploadSongData;
use App\Http\Requests\UploadSongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SongController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $mineOnly = $request->boolean('mine');
        $user = $request->user();

        $songs = Song::query()
            ->with([
                'uploader:id,name',
                'playlists' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->withExists(['playlists as is_in_my_library' => fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('is_primary', true),
            ])
            ->search($q)
            ->when($mineOnly, fn ($query) => $query->where('uploaded_by_user_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Songs/Index', [
            'songs' => SongResource::collection($songs),
            'filters' => ['q' => $q, 'mine' => $mineOnly],
            'playlists' => $user->playlists()
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get(['id', 'name', 'is_primary']),
        ]);
    }

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

    public function destroy(Request $request, Song $song): RedirectResponse
    {
        $this->authorize('delete', $song);

        $title = $song->title;
        $song->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Deleted \"{$title}\".",
        ]);

        return back();
    }
}
