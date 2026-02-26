<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->tinyInteger('is_trial')->default(0);
            $table->tinyInteger('is_official')->default(1);
            $table->tinyInteger('is_addon')->default(0);
            $table->json('bouquet_channels')->nullable();
            $table->json('bouquet_movies')->nullable();
            $table->json('bouquet_series')->nullable();
            $table->timestamps();
        });

        Schema::create('bouquets', function (Blueprint $table) {
            $table->id();
            $table->string('bouquet_name');
            $table->json('bouquet_channels')->nullable();
            $table->json('bouquet_movies')->nullable();
            $table->json('bouquet_series')->nullable();
            $table->json('bouquet_radios')->nullable();
            $table->integer('bouquet_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamp('exp_date')->nullable();
            $table->integer('max_connections')->default(1);
            $table->tinyInteger('is_trial')->default(0);
            $table->tinyInteger('admin_enabled')->default(1);
            $table->tinyInteger('is_restreamer')->default(0);
            $table->json('bouquet')->nullable();
            $table->string('allowed_ips')->nullable();
            $table->string('allowed_ua')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('member_group_id')->nullable()->constrained('member_groups')->nullOnDelete();
            $table->integer('active_connections')->default(0);
            $table->integer('max_connections_total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('added')->nullable();
            $table->timestamps();
        });

        Schema::create('line_package', function (Blueprint $table) {
            $table->foreignId('line_id')->constrained('lines')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->primary(['line_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_package');
        Schema::dropIfExists('lines');
        Schema::dropIfExists('bouquets');
        Schema::dropIfExists('packages');
    }
};
