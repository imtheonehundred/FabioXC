<?php

namespace App\Console\Commands;

use App\Domain\Server\Models\Server;
use Illuminate\Console\Command;

class ServerCommand extends Command
{
    protected $signature = 'server:health {--id= : Check specific server}';
    protected $description = 'Check server health and update status';

    public function handle(): int
    {
        $serverId = $this->option('id');
        $servers = $serverId ? Server::where('id', $serverId)->get() : Server::all();

        if ($servers->isEmpty()) { $this->warn('No servers found.'); return 0; }

        $this->table(['ID', 'Name', 'IP', 'Status', 'CPU', 'RAM', 'Disk', 'Clients'], $servers->map(fn ($s) => [
            $s->id, $s->server_name, $s->server_ip, $s->status_label,
            ($s->cpu_usage ?? 0) . '%', ($s->mem_usage ?? 0) . '%',
            ($s->disk_usage ?? 0) . '%', $s->total_clients,
        ])->toArray());

        $warnings = $servers->filter(fn ($s) => ($s->cpu_usage ?? 0) > 80 || ($s->mem_usage ?? 0) > 80 || ($s->disk_usage ?? 0) > 90);
        foreach ($warnings as $s) {
            $this->warn("WARNING: {$s->server_name} — CPU:{$s->cpu_usage}% RAM:{$s->mem_usage}% DISK:{$s->disk_usage}%");
        }

        $this->info("Total servers: {$servers->count()} | Online: " . $servers->where('status', 1)->count());
        return 0;
    }
}
