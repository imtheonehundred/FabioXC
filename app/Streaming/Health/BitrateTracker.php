<?php

namespace App\Streaming\Health;

use Illuminate\Support\Facades\Redis;

/**
 * Tracks per-stream bitrate and FPS in Redis.
 * Used for quality monitoring and degradation detection.
 */
class BitrateTracker
{
    private const KEY_PREFIX = 'stream:bitrate:';
    private const HISTORY_SIZE = 60;
    private const TTL = 300;

    public function record(int $streamId, int $bitrate, ?float $fps = null): void
    {
        try {
            $key = self::KEY_PREFIX . $streamId;
            $entry = json_encode([
                'bitrate' => $bitrate,
                'fps' => $fps,
                'ts' => time(),
            ]);

            Redis::lpush($key, $entry);
            Redis::ltrim($key, 0, self::HISTORY_SIZE - 1);
            Redis::expire($key, self::TTL);

            Redis::hset(self::KEY_PREFIX . 'current', (string) $streamId, $entry);
            Redis::expire(self::KEY_PREFIX . 'current', self::TTL);
        } catch (\Throwable) {
        }
    }

    public function getCurrent(int $streamId): ?array
    {
        try {
            $data = Redis::hget(self::KEY_PREFIX . 'current', (string) $streamId);
            return $data ? json_decode($data, true) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getHistory(int $streamId, int $count = 30): array
    {
        try {
            $entries = Redis::lrange(self::KEY_PREFIX . $streamId, 0, $count - 1);
            return array_map(fn ($e) => json_decode($e, true), $entries);
        } catch (\Throwable) {
            return [];
        }
    }

    public function getAverageBitrate(int $streamId, int $windowSeconds = 60): ?int
    {
        $history = $this->getHistory($streamId, self::HISTORY_SIZE);
        if (empty($history)) return null;

        $cutoff = time() - $windowSeconds;
        $recentEntries = array_filter($history, fn ($e) => ($e['ts'] ?? 0) >= $cutoff);

        if (empty($recentEntries)) return null;

        $sum = array_sum(array_column($recentEntries, 'bitrate'));
        return (int) ($sum / count($recentEntries));
    }

    public function getAllCurrentBitrates(): array
    {
        try {
            $all = Redis::hgetall(self::KEY_PREFIX . 'current');
            $result = [];
            foreach ($all as $streamId => $data) {
                $decoded = json_decode($data, true);
                if ($decoded) {
                    $result[(int) $streamId] = $decoded;
                }
            }
            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    public function clear(int $streamId): void
    {
        try {
            Redis::del(self::KEY_PREFIX . $streamId);
            Redis::hdel(self::KEY_PREFIX . 'current', (string) $streamId);
        } catch (\Throwable) {
        }
    }
}
