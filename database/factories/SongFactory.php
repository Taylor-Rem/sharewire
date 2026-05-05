<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Song>
 */
class SongFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'album' => fake()->optional()->words(2, true),
            'genre' => fake()->optional()->randomElement([
                'Rock', 'Jazz', 'Electronic', 'Folk', 'Hip-Hop', 'Classical', 'Ambient',
            ]),
            'duration_seconds' => fake()->numberBetween(60, 600),
            'file_path' => 'songs/'.fake()->uuid().'.mp3',
            'mime_type' => 'audio/mpeg',
            'uploaded_by_user_id' => User::factory(),
        ];
    }
}
