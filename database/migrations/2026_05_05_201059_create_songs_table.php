<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('file_path');
            $table->string('mime_type');
            $table->foreignId('uploaded_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->index(['title', 'artist']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
