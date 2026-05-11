<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('library_entries', 'playlist_songs');
    }

    public function down(): void
    {
        Schema::rename('playlist_songs', 'library_entries');
    }
};
