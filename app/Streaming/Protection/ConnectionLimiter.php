<?php

namespace App\Streaming\Protection;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

/**
 * Enforces max_connections per line using Redis for real-time tracking.
 * Each active connection registers a heartbeat; stale entries expire.
 */
class ConnectionLimiter
{
    private const KEY_PREFIX = 'connections:';
    private const HEARTBEAT_TTL = 60;

    public function check(int $lineId, int $maxConnections, string $connectionId): array
    {
        $key = self::KEY_PREFIX . $lineId;

        try {
            $this->cleanStale($key);

            $currentCount = Redis::scard($key);

            if (Redis::sismember($key, $connectionId)) {
                $this->heartbeat($lineId, $connectionId);
                return ['allowed' => true, 'active' => $currentCount];
            }

            if ($currentCount >= $maxConnections) {
                return [
                    'allowed' => false,
                    'reason' => "Max connections reached ({$currentCount}/{$maxConnections})",
                    'active' => $currentCount,
                    'max' => $maxConnections,
                ];
            }

            Redis::sadd($key, $connectionId);
            Redis::setex(self::KEY_PREFIX . "hb:{$lineId}:{$connectionId}", self::HEARTBEAT_TTL, time());
            Redis::expire($key, self::HEARTBEAT_TTL * 2);

            $this->syncToDb($lineId);

            return ['allowed' => true, 'active' => $currentCount + 1];
        } catch (\Throwable) {
            return ['allowed' => true, 'active' => 0, 'fallback' => true];
        }
    }

    public function heartbeat(int $lineId, string $connectionId): void
    {
        try {
            Redis::setex(self::KEY_PREFIX . "hb:{$lineId}:{$connectionId}", self::HEARTBEAT_TTL, time());
            Redis::expire(self::KEY_PREFIX . $lineId, self::HEARTBEAT_TTL * 2);
        } catch (\Throwable) {
        }
    }

    public function release(int $lineId, string $connectionId): void
    {
        try {
            $key = self::KEY_PREFIX . $lineId;
            Redis::srem($key, $connectionId);
            Redis::del(self::KEY_PREFIX . "hb:{$lineId}:{$connectionId}");
            $this->syncToDb($lineId);
        } catch (\Throwable) {
        }
    }

    public function getActiveCount(int $lineId): int
    {
        try {
            $this->cleanStale(self::KEY_PREFIX . $lineId);
            return (int) Redis::scard(self::KEY_PREFIX . $lineId);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function closeAll(int $lineId): void
    {
        try {
            $key = self::KEY_PREFIX . $lineId;
            $members = Redis::smembers($key);
            foreach ($members as $connId) {
                Redis::del(self::KEY_PREFIX . "hb:{$lineId}:{$connId}");
            }
            Redis::del($key);
            $this->syncToDb($lineId);
        } catch (\Throwable) {
        }
    }

    public function generateConnectionId(string $ip, string $userAgent, int $streamId): string
    {
        return hash('crc32b', "{$ip}|{$userAgent}|{$streamId}|" . getmypid());
    }

    private function cleanStale(string $key): void
    {
        $members = Redis::smembers($key);
        $lineId = str_replace(self::KEY_PREFIX, '', $key);

        foreach ($members as $connId) {
            $hbKey = self::KEY_PREFIX . "hb:{$lineId}:{$connId}";
            if (!Redis::exists($hbKey)) {
                Redis::srem($key, $connId);
            }
        }
    }

    private function syncToDb(int $lineId): void
    {
        try {
            $count = (int) Redis::scard(self::KEY_PREFIX . $lineId);
            DB::table('lines')->where('id', $lineId)->update(['active_connections' => $count]);
        } catch (\Throwable) {
        }
    }
}
