<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleanupCommand extends Command
{
    protected $signature = 'xcvm:cleanup {--days=30 : Delete records older than N days} {--dry-run : Show what would be deleted}';
    protected $description = 'Cleanup old logs, tmp files, and stale data';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Cleaning up data older than {$days} days" . ($dryRun ? ' (DRY RUN)' : ''));

        $logCount = DB::table('activity_logs')->where('created_at', '<', $cutoff)->count();
        $this->line("Activity logs to delete: {$logCount}");
        if (!$dryRun && $logCount > 0) {
            DB::table('activity_logs')->where('created_at', '<', $cutoff)->delete();
        }

        $closedTickets = DB::table('tickets')->where('status', 'closed')->where('updated_at', '<', $cutoff)->count();
        $this->line("Closed tickets to delete: {$closedTickets}");
        if (!$dryRun && $closedTickets > 0) {
            DB::table('tickets')->where('status', 'closed')->where('updated_at', '<', $cutoff)->delete();
        }

        $tmpPath = storage_path('app/tmp');
        if (is_dir($tmpPath)) {
            $tmpFiles = collect(File::files($tmpPath))->filter(fn ($f) => $f->getMTime() < $cutoff->timestamp);
            $this->line("Tmp files to delete: {$tmpFiles->count()}");
            if (!$dryRun) { foreach ($tmpFiles as $f) File::delete($f->getPathname()); }
        }

        $this->info('Cleanup complete.');
        return 0;
    }
}
