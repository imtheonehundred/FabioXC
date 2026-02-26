<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address')->unique();
            $table->string('device_type')->default('mag');
            $table->foreignId('line_id')->nullable()->constrained('lines')->nullOnDelete();
            $table->string('model')->nullable();
            $table->string('ip_address')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('admin_enabled')->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transcode_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('video_codec')->default('copy');
            $table->string('audio_codec')->default('copy');
            $table->string('video_bitrate')->nullable();
            $table->string('audio_bitrate')->nullable();
            $table->string('resolution')->nullable();
            $table->integer('fps')->nullable();
            $table->string('preset')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcode_profiles');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('devices');
    }
};
