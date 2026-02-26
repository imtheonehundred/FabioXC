<?php

namespace App\Streaming\Protection;

use Illuminate\Support\Facades\Redis;

/**
 * Detects suspicious restreaming patterns:
 * - Too many unique IPs per line in a short window
 * - Abnormally high request rates
 * - Known restreaming user-agent signatures
 */
class RestreamDetector
{
    private const IP_WINDOW = 3600;
    private const MAX_UNIQUE_IPS = 10;
    private const RATE_WINDOW = 60;
    private const MAX_REQUESTS_PER_MINUTE = 120;

    private const SUSPICIOUS_UA_PATTERNS = [
        'ffmpeg', 'libav', 'vlc/3', 'streamlink', 'gstreamer',
        'wget', 'curl/', 'python-requests', 'httpclient',
    ];

    public function check(int $lineId, string $clientIp, string $userAgent): array
    {
        $flags = [];

        if ($this->hasSuspiciousUserAgent($userAgent)) {
            $flags[] = 'suspicious_user_agent';
        }

        try {
            if ($this->hasTooManyUniqueIps($lineId, $clientIp)) {
                $flags[] = 'too_many_unique_ips';
            }

            if ($this->hasHighRequestRate($lineId)) {
                $flags[] = 'high_request_rate';
            }
        } catch (\Throwable) {
            // Redis unavailable — skip checks
        }

        $suspicious = count($flags) >= 2;

        return [
            'suspicious' => $suspicious,
            'flags' => $flags,
            'action' => $suspicious ? 'flag' : 'allow',
        ];
    }

    private function hasSuspiciousUserAgent(string $userAgent): bool
    {
        $ua = strtolower($userAgent);
        foreach (self::SUSPICIOUS_UA_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function hasTooManyUniqueIps(int $lineId, string $clientIp): bool
    {
        $key = "restream:ips:{$lineId}";

        Redis::sadd($key, $clientIp);
        Redis::expire($key, self::IP_WINDOW);

        return Redis::scard($key) > self::MAX_UNIQUE_IPS;
    }

    private function hasHighRequestRate(int $lineId): bool
    {
        $key = "restream:rate:{$lineId}";

        $count = Redis::incr($key);
        if ($count === 1) {
            Redis::expire($key, self::RATE_WINDOW);
        }

        return $count > self::MAX_REQUESTS_PER_MINUTE;
    }
}
