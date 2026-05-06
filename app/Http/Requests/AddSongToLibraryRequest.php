<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddSongToLibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated user can add any song to their library.
        // Route-model binding guarantees the song exists; the auth
        // middleware guarantees we have a user.
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
