<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Song
 */
class SongResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'album' => $this->album,
            'genre' => $this->genre,
            'duration_seconds' => $this->duration_seconds,
            'mime_type' => $this->mime_type,
            'uploader' => [
                'id' => $this->uploaded_by_user_id,
                'name' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            ],
            'is_in_my_library' => (bool) ($this->is_in_my_library ?? false),
            'is_uploader' => $request->user()?->id === $this->uploaded_by_user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
