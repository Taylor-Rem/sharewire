<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlaylistSong>
 */
class PlaylistSongFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'song_id' => Song::factory(),
            'position' => null,
            'added_at' => now(),
        ];
    }
}
