<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SongObserver;
use Database\Factories\SongFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'title',
    'artist',
    'album',
    'genre',
    'duration_seconds',
    'file_path',
    'mime_type',
    'uploaded_by_user_id',
])]
#[ObservedBy([SongObserver::class])]
class Song extends Model
{
    /** @use HasFactory<SongFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function inLibrariesOf(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'library_entries')
            ->using(LibraryEntry::class)
            ->withPivot(['id', 'position', 'added_at'])
            ->withTimestamps();
    }

    /**
     * Filter songs whose title, artist, album, or genre matches the given term.
     * No-op when the term is empty.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        if ($term === '') {
            return;
        }

        $like = '%'.str_replace('%', '\\%', $term).'%';

        $query->where(fn (Builder $inner) => $inner
            ->where('title', 'like', $like)
            ->orWhere('artist', 'like', $like)
            ->orWhere('album', 'like', $like)
            ->orWhere('genre', 'like', $like)
        );
    }
}
