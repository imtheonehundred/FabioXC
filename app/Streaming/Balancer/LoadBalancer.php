<?php

namespace App\Streaming\Balancer;

use Illuminate\Support\Facades\DB;

/**
 * Selects the optimal server for a streaming request based on
 * CPU, memory, connection count, and stream assignment.
 * Includes redirect strategy (< 150 lines combined per §2.4).
 */
class LoadBalancer
{
    public function selectServer(int $streamId, ?int $preferredServerId = null): ?array
    {
        if ($preferredServerId) {
            $preferred = $this->getServer($preferredServerId);
            if ($preferred && $preferred->status === 1) {
                return $this->toArray($preferred);
            }
        }

        $assignedServer = DB::table('streams')
            ->join('servers', 'streams.server_id', '=', 'servers.id')
            ->where('streams.id', $streamId)
            ->where('servers.status', 1)
            ->select('servers.*')
            ->first();

        if ($assignedServer) {
            return $this->toArray($assignedServer);
        }

        return $this->selectLeastLoaded();
    }

    public function selectLeastLoaded(): ?array
    {
        $server = DB::table('servers')
            ->where('status', 1)
            ->orderByRaw('(COALESCE(cpu_usage, 100) + COALESCE(mem_usage, 100)) / 2 ASC')
            ->orderBy('total_clients', 'asc')
            ->first();

        return $server ? $this->toArray($server) : null;
    }

    public function selectForVod(int $vodId, string $type = 'movie'): ?array
    {
        $table = $type === 'episode' ? 'episodes' : 'movies';

        $assignedServer = DB::table($table)
            ->join('servers', "{$table}.server_id", '=', 'servers.id')
            ->where("{$table}.id", $vodId)
            ->where('servers.status', 1)
            ->select('servers.*')
            ->first();

        if ($assignedServer) {
            return $this->toArray($assignedServer);
        }

        return $this->selectLeastLoaded();
    }

    public function buildRedirectUrl(array $server, string $path, array $queryParams = []): string
    {
        $host = $server['domain_name'] ?: $server['server_ip'];
        $port = $server['http_port'] ?? 80;
        $scheme = 'http';

        $url = "{$scheme}://{$host}";
        if ($port !== 80 && $port !== 443) {
            $url .= ":{$port}";
        }

        $url .= '/' . ltrim($path, '/');

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    public function shouldRedirect(array $currentServer, array $targetServer): bool
    {
        return ($currentServer['id'] ?? 0) !== ($targetServer['id'] ?? 0);
    }

    public function getAllServersHealth(): array
    {
        return DB::table('servers')
            ->where('status', 1)
            ->orderBy('total_clients')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->server_name,
                'ip' => $s->server_ip,
                'cpu' => $s->cpu_usage ?? 0,
                'memory' => $s->mem_usage ?? 0,
                'clients' => $s->total_clients,
                'score' => $this->calculateScore($s),
            ])
            ->toArray();
    }

    private function calculateScore(object $server): float
    {
        $cpu = $server->cpu_usage ?? 100;
        $mem = $server->mem_usage ?? 100;
        $clients = $server->total_clients ?? 0;

        // Lower score = better server
        return ($cpu * 0.4) + ($mem * 0.3) + (min($clients, 1000) / 10 * 0.3);
    }

    private function getServer(int $id): ?object
    {
        return DB::table('servers')->where('id', $id)->first();
    }

    private function toArray(object $server): array
    {
        return [
            'id' => $server->id,
            'server_name' => $server->server_name,
            'server_ip' => $server->server_ip,
            'domain_name' => $server->domain_name,
            'http_port' => $server->http_port,
            'rtmp_port' => $server->rtmp_port,
            'status' => $server->status,
            'cpu_usage' => $server->cpu_usage,
            'mem_usage' => $server->mem_usage,
            'total_clients' => $server->total_clients,
        ];
    }
}
