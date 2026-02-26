<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheCommand extends Command
{
    protected $signature = 'xcvm:cache {action=status : clear|rebuild|status}';
    protected $description = 'Manage application cache';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'clear' => $this->clearCache(),
            'rebuild' => $this->rebuildCache(),
            'status' => $this->showStatus(),
            default => $this->error("Unknown action. Use: clear, rebuild, status") ?? 1,
        };
    }

    private function clearCache(): int
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        $this->info('All caches cleared.');
        return 0;
    }

    private function rebuildCache(): int
    {
        $this->clearCache();
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $this->info('Caches rebuilt.');
        return 0;
    }

    private function showStatus(): int
    {
        $this->info('Cache driver: ' . config('cache.default'));
        $this->info('Config cached: ' . (file_exists(base_path('bootstrap/cache/config.php')) ? 'Yes' : 'No'));
        $this->info('Routes cached: ' . (file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'Yes' : 'No'));
        return 0;
    }
}
