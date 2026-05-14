<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Data\SongData;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Thin shim over SongData. The Data class is the source of truth for the wire
 * shape and is what generates the TypeScript types. This Resource exists only
 * to leverage Laravel's AnonymousResourceCollection pagination wrapping
 * ({ data, meta, links }) when used as ::collection($paginator).
 *
 * @mixin Song
 */
class SongResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return SongData::fromModel($this->resource, $request->user())->toArray();
    }
}
