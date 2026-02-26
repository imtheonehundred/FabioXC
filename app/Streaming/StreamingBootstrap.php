<?php

namespace App\Streaming;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight bootstrap for the streaming context.
 * Loads only what's needed for video delivery — no admin services, no modules, no Inertia.
 *
 * Performance budget (ARCHITECTURE.md §10.6):
 *   Bootstrap: < 5ms | Auth: < 10ms | Lookup: < 5ms | Delivery: < 10ms
 *   Total target: < 30ms (max 50ms p99)
 */
class StreamingBootstrap
{
    private static bool $booted = false;
    private static array $config = [];

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$config = self::loadConfig();
        self::$booted = true;
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    public static function getServerInfo(): array
    {
        static $server = null;
        if ($server === null) {
            $server = DB::table('servers')
                ->where('is_main', 1)
                ->first();
            if (!$server) {
                $server = DB::table('servers')
                    ->where('status', 1)
                    ->first();
            }
        }
        return $server ? (array) $server : [];
    }

    private static function loadConfig(): array
    {
        $settings = [];
        try {
            $rows = DB::table('settings')->get(['key', 'value']);
            foreach ($rows as $row) {
                $settings[$row->key] = $row->value;
            }
        } catch (\Throwable $e) {
            Log::error('StreamingBootstrap: Failed to load settings: ' . $e->getMessage());
        }
        return $settings;
    }
}
