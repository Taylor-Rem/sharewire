<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $userIds = DB::table('users')->pluck('id');

            foreach ($userIds as $userId) {
                $playlistId = DB::table('playlists')->insertGetId([
                    'name' => 'My Library',
                    'user_id' => $userId,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('playlist_songs')
                    ->where('user_id', $userId)
                    ->update(['playlist_id' => $playlistId]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('playlist_songs')->update(['playlist_id' => null]);
            DB::table('playlists')->where('is_primary', true)->delete();
        });
    }
};
