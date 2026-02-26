<?php

namespace App\Console\Commands;

use App\Domain\Stream\Models\Stream;
use Illuminate\Console\Command;

class MonitorCommand extends Command
{
    protected $signature = 'streams:monitor {--interval=30 : Check interval in seconds} {--once : Run once and exit}';
    protected $description = 'Monitor stream health and auto-restart failed streams';

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $once = $this->option('once');

        $this->info('Stream monitor started. Interval: ' . $interval . 's');

        do {
            $this->checkStreams();
            if (!$once) { sleep($interval); }
        } while (!$once);

        return 0;
    }

    private function checkStreams(): void
    {
        $streams = Stream::where('admin_enabled', 1)->where('status', 1)->get();
        $issues = 0;

        foreach ($streams as $stream) {
            if ($stream->pid && !$this->isProcessRunning($stream->pid)) {
                $this->warn("Stream #{$stream->id} ({$stream->stream_display_name}) - PID {$stream->pid} not running");
                $stream->update(['status' => 3, 'pid' => null]);
                $issues++;

                $autoRestart = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'auto_restart_streams')->value('value');
                if ($autoRestart) {
                    $stream->update(['status' => 2]);
                    $this->info("  Auto-restarting stream #{$stream->id}...");
                    $stream->update(['status' => 1, 'started_at' => now()]);
                }
            }
        }

        $this->line('[' . now()->format('H:i:s') . "] Checked {$streams->count()} streams, {$issues} issues");
    }

    private function isProcessRunning(int $pid): bool
    {
        if (!function_exists('posix_kill')) return true;
        return posix_kill($pid, 0);
    }
}
