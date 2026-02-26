<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key', 64)->nullable()->unique()->after('admin_permissions');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->string('server_key', 64)->nullable()->unique()->after('last_check_at');
        });

        // Generate api_key for existing users
        foreach (\App\Models\User::all() as $user) {
            $user->update(['api_key' => Str::random(48)]);
        }

        // Generate server_key for existing servers
        foreach (\App\Domain\Server\Models\Server::all() as $server) {
            $server->update(['server_key' => Str::random(48)]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_key');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('server_key');
        });
    }
};
