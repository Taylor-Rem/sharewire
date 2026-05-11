<?php

namespace App\Models;

use Database\Factories\PlaylistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'user_id',
    'is_primary',
])]

class Playlist extends Model
{
    /** @use HasFactory<PlaylistFactory> */
    use HasFactory;
}
