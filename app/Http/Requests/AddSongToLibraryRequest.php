<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSongToLibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated user can add any song to one of their own
        // playlists. Ownership of the chosen playlist is enforced by the
        // exists() rule below, not here.
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'playlist_id' => [
                'required',
                'integer',
                Rule::exists('playlists', 'id')->where('user_id', $this->user()?->id),
            ],
        ];
    }
}
