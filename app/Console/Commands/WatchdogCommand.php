<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WatchdogCommand extends Command
{
    protected $signature = 'xcvm:watchdog {--interval=60 : Check interval in seconds}';
    protected $description = 'Process watchdog - monitors critical services and restarts if needed';

    private array $criticalProcesses = ['nginx', 'php-fpm', 'redis-server'];

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $this->info('Watchdog started. Monitoring critical processes.');

        while (true) {
            foreach ($this->criticalProcesses as $process) {
                if (!$this->isRunning($process)) {
                    $this->error("{$process} is NOT running!");
                    DB::table('activity_logs')->insert([
                        'action' => 'watchdog.alert', 'description' => "{$process} not running",
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } else {
                    $this->line("[" . now()->format('H:i:s') . "] {$process}: OK");
                }
            }
            sleep($interval);
        }
    }

    private function isRunning(string $processName): bool
    {
        $output = shell_exec("pgrep -x {$processName} 2>/dev/null");
        return !empty(trim($output ?? ''));
    }
}
