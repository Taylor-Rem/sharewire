<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LibraryEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'user_id',
    'song_id',
    'position',
    'added_at',
])]
class LibraryEntry extends Pivot
{
    /** @use HasFactory<LibraryEntryFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'library_entries';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'added_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
