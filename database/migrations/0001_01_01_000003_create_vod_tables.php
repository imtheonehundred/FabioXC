<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('stream_display_name');
            $table->text('stream_source');
            $table->string('tmdb_id')->nullable();
            $table->text('cover')->nullable();
            $table->text('plot')->nullable();
            $table->string('cast')->nullable();
            $table->string('director')->nullable();
            $table->string('genre')->nullable();
            $table->string('rating')->nullable();
            $table->integer('rating_5based')->nullable();
            $table->string('release_date')->nullable();
            $table->string('duration')->nullable();
            $table->string('youtube_trailer')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('stream_categories')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('admin_enabled')->default(1);
            $table->string('container_extension')->nullable();
            $table->bigInteger('target_container')->nullable();
            $table->timestamp('added')->nullable();
            $table->timestamps();
        });

        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('cover')->nullable();
            $table->text('plot')->nullable();
            $table->string('cast')->nullable();
            $table->string('genre')->nullable();
            $table->string('tmdb_id')->nullable();
            $table->string('rating')->nullable();
            $table->integer('rating_5based')->nullable();
            $table->string('release_date')->nullable();
            $table->string('youtube_trailer')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('stream_categories')->nullOnDelete();
            $table->tinyInteger('admin_enabled')->default(1);
            $table->integer('last_modified')->nullable();
            $table->timestamps();
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
            $table->integer('season_number')->default(1);
            $table->integer('episode_number')->default(1);
            $table->string('title')->nullable();
            $table->text('stream_source');
            $table->text('cover')->nullable();
            $table->text('plot')->nullable();
            $table->string('duration')->nullable();
            $table->string('rating')->nullable();
            $table->string('container_extension')->nullable();
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('admin_enabled')->default(1);
            $table->timestamp('added')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('series');
        Schema::dropIfExists('movies');
    }
};
