<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use Illuminate\Support\Collection;

/**
 * Generate cron configuration lines for stream monitoring, restarts, and EPG.
 * Used by admin settings to export or install crontab.
 */
class CronGenerator
{
    public function __construct(
        private string $phpPath = 'php',
        private string $artisanPath = ''
    ) {
        if ($this->artisanPath === '') {
            $this->artisanPath = base_path('artisan');
        }
    }

    public function setPhpPath(string $path): self
    {
        $this->phpPath = $path;
        return $this;
    }

    public function setArtisanPath(string $path): self
    {
        $this->artisanPath = $path;
        return $this;
    }

    /**
     * Generate cron entries for Laravel scheduler (single entry) or legacy-style per-task entries.
     *
     * @return array<int, string>
     */
    public function generateForScheduler(bool $schedulerOnly = true): array
    {
        if ($schedulerOnly) {
            return [
                '* * * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' schedule:run >> /dev/null 2>&1',
            ];
        }

        $lines = [];
        $lines[] = '# Streams health check and restart';
        $lines[] = '* * * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' streams:watch >> /dev/null 2>&1';
        $lines[] = '# Servers status';
        $lines[] = '* * * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' server:watch >> /dev/null 2>&1';
        $lines[] = '# Cache refresh';
        $lines[] = '*/5 * * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' cache:refresh >> /dev/null 2>&1';
        $lines[] = '# EPG update';
        $lines[] = '0 * * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' schedule:run >> /dev/null 2>&1';
        $lines[] = '# Cleanup old logs';
        $lines[] = '0 0 * * * ' . $this->phpPath . ' ' . $this->artisanPath . ' cleanup:run >> /dev/null 2>&1';

        return $lines;
    }

    /**
     * Generate a single crontab line for the scheduler.
     */
    public function getSchedulerCronLine(): string
    {
        $lines = $this->generateForScheduler(true);
        return $lines[0] ?? '';
    }

    /**
     * Full cron block as string (for display or export).
     */
    public function generateFullCronBlock(bool $useSchedulerOnly = true): string
    {
        return implode("\n", $this->generateForScheduler($useSchedulerOnly));
    }
}
