<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('blocked_isps', function (Blueprint $table) {
            $table->id();
            $table->string('isp_name');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('blocked_uas', function (Blueprint $table) {
            $table->id();
            $table->string('user_agent');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('blocked_uas');
        Schema::dropIfExists('blocked_isps');
        Schema::dropIfExists('blocked_ips');
    }
};
