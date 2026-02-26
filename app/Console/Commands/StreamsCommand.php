<?php

namespace App\Console\Commands;

use App\Domain\Stream\Models\Stream;
use Illuminate\Console\Command;

class StreamsCommand extends Command
{
    protected $signature = 'streams:manage {action : start|stop|restart|status} {--stream= : Specific stream ID} {--all : Apply to all streams}';
    protected $description = 'Manage streams - start, stop, restart, or check status';

    public function handle(): int
    {
        $action = $this->argument('action');
        $streamId = $this->option('stream');

        if ($streamId) {
            $stream = Stream::find($streamId);
            if (!$stream) { $this->error("Stream #{$streamId} not found."); return 1; }
            $this->processStream($stream, $action);
        } elseif ($this->option('all')) {
            $streams = Stream::where('admin_enabled', 1)->get();
            $this->info("Processing {$streams->count()} streams...");
            $bar = $this->output->createProgressBar($streams->count());
            foreach ($streams as $stream) { $this->processStream($stream, $action); $bar->advance(); }
            $bar->finish(); $this->newLine();
        } else {
            $this->table(['ID', 'Name', 'Status', 'PID', 'Viewers'], Stream::all()->map(fn ($s) => [
                $s->id, $s->stream_display_name, $s->status_label, $s->pid ?? '—', $s->current_viewers,
            ])->toArray());
        }

        return 0;
    }

    private function processStream(Stream $stream, string $action): void
    {
        match ($action) {
            'start' => $this->startStream($stream),
            'stop' => $this->stopStream($stream),
            'restart' => $this->restartStream($stream),
            'status' => $this->info("{$stream->stream_display_name}: {$stream->status_label}"),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    private function startStream(Stream $stream): void
    {
        $stream->update(['status' => 1, 'started_at' => now()]);
        $this->info("Started: {$stream->stream_display_name}");
    }

    private function stopStream(Stream $stream): void
    {
        if ($stream->pid) { @posix_kill($stream->pid, SIGTERM); }
        $stream->update(['status' => 0, 'pid' => null]);
        $this->info("Stopped: {$stream->stream_display_name}");
    }

    private function restartStream(Stream $stream): void
    {
        $this->stopStream($stream);
        usleep(500000);
        $this->startStream($stream);
    }
}
