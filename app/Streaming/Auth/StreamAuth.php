<?php

namespace App\Streaming\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Validates that a line has access to a specific stream.
 * Checks: enabled, expiry, bouquet membership, IP/UA restrictions.
 */
class StreamAuth
{
    public function validate(array $lineData, int $streamId, string $clientIp, string $userAgent): array
    {
        if (!$lineData['admin_enabled']) {
            return $this->deny('Account is disabled');
        }

        if ($lineData['exp_date'] && Carbon::parse($lineData['exp_date'])->isPast()) {
            return $this->deny('Account has expired');
        }

        if (!empty($lineData['allowed_ips'])) {
            $allowedIps = array_map('trim', explode(',', $lineData['allowed_ips']));
            if (!in_array($clientIp, $allowedIps)) {
                return $this->deny('IP not allowed');
            }
        }

        if (!empty($lineData['allowed_ua'])) {
            $allowedUa = array_map('trim', explode(',', $lineData['allowed_ua']));
            $uaAllowed = false;
            foreach ($allowedUa as $pattern) {
                if (stripos($userAgent, $pattern) !== false) {
                    $uaAllowed = true;
                    break;
                }
            }
            if (!$uaAllowed) {
                return $this->deny('User-Agent not allowed');
            }
        }

        $stream = DB::table('streams')
            ->where('id', $streamId)
            ->where('admin_enabled', 1)
            ->first();

        if (!$stream) {
            return $this->deny('Stream not found or disabled');
        }

        if (!$this->checkBouquetAccess($lineData['bouquet'], $stream, $streamId)) {
            return $this->deny('No access to this stream');
        }

        return [
            'allowed' => true,
            'stream' => (array) $stream,
            'line' => $lineData,
        ];
    }

    public function validateVod(array $lineData, int $vodId, string $type, string $clientIp, string $userAgent): array
    {
        if (!$lineData['admin_enabled']) {
            return $this->deny('Account is disabled');
        }

        if ($lineData['exp_date'] && Carbon::parse($lineData['exp_date'])->isPast()) {
            return $this->deny('Account has expired');
        }

        $table = $type === 'series' ? 'episodes' : 'movies';
        $vod = DB::table($table)->where('id', $vodId)->where('admin_enabled', 1)->first();

        if (!$vod) {
            return $this->deny('Content not found or disabled');
        }

        return [
            'allowed' => true,
            'vod' => (array) $vod,
            'line' => $lineData,
        ];
    }

    private function checkBouquetAccess(array $lineBouquets, object $stream, int $streamId): bool
    {
        if (empty($lineBouquets)) {
            return true;
        }

        $bouquets = DB::table('bouquets')
            ->whereIn('id', $lineBouquets)
            ->get();

        foreach ($bouquets as $bouquet) {
            $channels = json_decode($bouquet->bouquet_channels, true) ?? [];
            if (in_array($streamId, $channels)) {
                return true;
            }
        }

        return false;
    }

    private function deny(string $reason): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
        ];
    }
}
