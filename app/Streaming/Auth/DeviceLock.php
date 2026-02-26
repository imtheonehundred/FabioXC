<?php

namespace App\Streaming\Auth;

use Illuminate\Support\Facades\Redis;

/**
 * Binds a line to a specific device using IP + User-Agent hash.
 * Prevents credential sharing across different devices.
 */
class DeviceLock
{
    private const KEY_PREFIX = 'device_lock:';
    private const LOCK_TTL = 3600;

    public function check(int $lineId, string $clientIp, string $userAgent): array
    {
        $deviceHash = $this->generateHash($clientIp, $userAgent);
        $key = self::KEY_PREFIX . $lineId;

        try {
            $stored = Redis::get($key);

            if ($stored === null) {
                Redis::setex($key, self::LOCK_TTL, $deviceHash);
                return ['allowed' => true, 'device_hash' => $deviceHash];
            }

            if ($stored === $deviceHash) {
                Redis::expire($key, self::LOCK_TTL);
                return ['allowed' => true, 'device_hash' => $deviceHash];
            }

            return [
                'allowed' => false,
                'reason' => 'Device mismatch - stream is locked to another device',
                'device_hash' => $deviceHash,
            ];
        } catch (\Throwable $e) {
            // Redis unavailable — allow through (fail-open for streaming)
            return ['allowed' => true, 'device_hash' => $deviceHash, 'fallback' => true];
        }
    }

    public function release(int $lineId): void
    {
        try {
            Redis::del(self::KEY_PREFIX . $lineId);
        } catch (\Throwable) {
        }
    }

    public function refresh(int $lineId): void
    {
        try {
            Redis::expire(self::KEY_PREFIX . $lineId, self::LOCK_TTL);
        } catch (\Throwable) {
        }
    }

    private function generateHash(string $ip, string $userAgent): string
    {
        return hash('sha256', $ip . '|' . $userAgent);
    }
}
