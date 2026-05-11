<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playlist_songs', function (Blueprint $table) {
            $table->dropUnique('library_entries_user_id_song_id_unique');
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('playlist_songs', function (Blueprint $table) {
            $table->foreignId('playlist_id')->nullable(false)->change();
            $table->unique(['playlist_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::table('playlist_songs', function (Blueprint $table) {
            $table->dropUnique(['playlist_id', 'song_id']);
            $table->foreignId('playlist_id')->nullable()->change();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unique(['user_id', 'song_id'], 'library_entries_user_id_song_id_unique');
        });
    }
};
