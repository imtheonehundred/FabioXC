<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->boolean('tv_archive')->default(false)->after('admin_enabled');
            $table->unsignedInteger('tv_archive_duration')->default(0)->after('tv_archive')->comment('Archive duration in hours');
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->dropColumn(['tv_archive', 'tv_archive_duration']);
        });
    }
};
