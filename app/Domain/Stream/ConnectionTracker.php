<?php

namespace App\Domain\Stream;

use App\Streaming\Protection\ConnectionLimiter;
use Illuminate\Support\Facades\Redis;

/**
 * Tracks active connections per line and stream using Redis.
 * Provides read-only queries for admin dashboard and API (connection counts, heartbeat).
 */
class ConnectionTracker
{
    private const KEY_PREFIX = 'connections:';
    private const HB_PREFIX = 'connections:hb:';

    public function __construct(
        private ConnectionLimiter $limiter
    ) {}

    public function getActiveCountForLine(int $lineId): int
    {
        return $this->limiter->getActiveCount($lineId);
    }

    /**
     * Get connection IDs (opaque) for a line for display or debugging.
     *
     * @return array<int, string>
     */
    public function getConnectionIdsForLine(int $lineId): array
    {
        try {
            $key = self::KEY_PREFIX . $lineId;
            $members = Redis::smembers($key);
            return array_values($members);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Close all connections for a line (e.g. admin action).
     */
    public function closeAllForLine(int $lineId): void
    {
        $this->limiter->closeAll($lineId);
    }

    /**
     * Release a single connection by ID.
     */
    public function release(int $lineId, string $connectionId): void
    {
        $this->limiter->release($lineId, $connectionId);
    }

    /**
     * Generate connection ID (same as ConnectionLimiter) for consistency.
     */
    public function generateConnectionId(string $ip, string $userAgent, int $streamId): string
    {
        return $this->limiter->generateConnectionId($ip, $userAgent, $streamId);
    }
}
