<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('song_id')
                ->constrained('songs')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_entries');
    }
};
