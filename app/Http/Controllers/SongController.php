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

        $libraryIds = $request->user()->library()->pluck('songs.id')->all();

        $songs = Song::query()
            ->with('uploader:id,name')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $like = '%'.str_replace('%', '\\%', $q).'%';
                    $inner->where('title', 'like', $like)
                        ->orWhere('artist', 'like', $like)
                        ->orWhere('album', 'like', $like)
                        ->orWhere('genre', 'like', $like);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $songs->getCollection()->each(function (Song $song) use ($libraryIds): void {
            $song->setAttribute('is_in_my_library', in_array($song->id, $libraryIds, true));
        });

        return Inertia::render('Songs/Index', [
            'songs' => SongResource::collection($songs),
            'filters' => ['q' => $q],
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
}
