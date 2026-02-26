<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_type')->default('live');
            $table->foreignId('parent_id')->nullable()->constrained('stream_categories')->nullOnDelete();
            $table->integer('cat_order')->default(0);
            $table->timestamps();
        });

        Schema::create('epg', function (Blueprint $table) {
            $table->id();
            $table->string('epg_name');
            $table->text('epg_url');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('stream_display_name');
            $table->text('stream_source');
            $table->string('type')->default('live');
            $table->tinyInteger('status')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('stream_categories')->nullOnDelete();
            $table->foreignId('epg_id')->nullable()->constrained('epg')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->integer('pid')->nullable();
            $table->string('stream_icon')->nullable();
            $table->text('notes')->nullable();
            $table->string('custom_ffmpeg')->nullable();
            $table->tinyInteger('transcode')->default(0);
            $table->integer('stream_order')->default(0);
            $table->integer('current_viewers')->default(0);
            $table->tinyInteger('admin_enabled')->default(1);
            $table->integer('bitrate')->nullable();
            $table->string('resolution')->nullable();
            $table->string('codec')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('added')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
        Schema::dropIfExists('epg');
        Schema::dropIfExists('stream_categories');
    }
};
