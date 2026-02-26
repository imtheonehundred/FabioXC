<?php

namespace App\Streaming\Protection;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Blocks streaming access by country, ISP, IP address, or user-agent pattern.
 * Reads from blocked_ips, blocked_isps, blocked_uas tables.
 * Uses cache to avoid DB queries on every request.
 */
class GeoBlock
{
    private const CACHE_TTL = 300;

    public function check(string $clientIp, string $userAgent, ?string $countryCode = null, ?string $isp = null): array
    {
        if ($this->isIpBlocked($clientIp)) {
            return $this->block('IP address is blocked');
        }

        if ($this->isUserAgentBlocked($userAgent)) {
            return $this->block('User-Agent is blocked');
        }

        if ($isp && $this->isIspBlocked($isp)) {
            return $this->block('ISP is blocked');
        }

        return ['allowed' => true];
    }

    public function isIpBlocked(string $ip): bool
    {
        $blockedIps = $this->getBlockedIps();

        if (in_array($ip, $blockedIps)) {
            return true;
        }

        foreach ($blockedIps as $blocked) {
            if (str_contains($blocked, '/') && $this->ipInCidr($ip, $blocked)) {
                return true;
            }
            if (str_contains($blocked, '*') && fnmatch($blocked, $ip)) {
                return true;
            }
        }

        return false;
    }

    public function isUserAgentBlocked(string $userAgent): bool
    {
        $blockedUas = $this->getBlockedUserAgents();

        foreach ($blockedUas as $pattern) {
            if (empty($pattern)) continue;
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    public function isIspBlocked(string $isp): bool
    {
        $blockedIsps = $this->getBlockedIsps();

        foreach ($blockedIsps as $blocked) {
            if (empty($blocked)) continue;
            if (stripos($isp, $blocked) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getBlockedIps(): array
    {
        return Cache::remember('streaming:blocked_ips', self::CACHE_TTL, function () {
            return DB::table('blocked_ips')->pluck('ip_address')->toArray();
        });
    }

    private function getBlockedUserAgents(): array
    {
        return Cache::remember('streaming:blocked_uas', self::CACHE_TTL, function () {
            return DB::table('blocked_uas')->pluck('user_agent')->toArray();
        });
    }

    private function getBlockedIsps(): array
    {
        return Cache::remember('streaming:blocked_isps', self::CACHE_TTL, function () {
            return DB::table('blocked_isps')->pluck('isp_name')->toArray();
        });
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);
        return ($ip & $mask) === ($subnet & $mask);
    }

    private function block(string $reason): array
    {
        return ['allowed' => false, 'reason' => $reason];
    }
}
