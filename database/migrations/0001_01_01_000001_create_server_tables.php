<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name');
            $table->string('server_ip');
            $table->string('domain_name')->nullable();
            $table->integer('http_port')->default(80);
            $table->integer('rtmp_port')->default(1935);
            $table->integer('total_clients')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_main')->default(0);
            $table->decimal('cpu_usage', 5, 2)->nullable();
            $table->decimal('mem_usage', 5, 2)->nullable();
            $table->decimal('disk_usage', 5, 2)->nullable();
            $table->string('uptime')->nullable();
            $table->integer('network_rx')->nullable();
            $table->integer('network_tx')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
