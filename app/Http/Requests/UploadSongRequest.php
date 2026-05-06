<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Song;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Song::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:64'],
            'audio' => [
                'required',
                'file',
                'mimes:mp3',
                'mimetypes:audio/mpeg',
                'max:102400', // 100 MB (matches PHP/nginx limits in deployment)
            ],
        ];
    }
}
