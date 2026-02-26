<?php

namespace App\Console\Commands;

use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\StreamProcess;
use Illuminate\Console\Command;

class StreamsCommand extends Command
{
    protected $signature = 'streams:manage {action : start|stop|restart|status} {--stream= : Specific stream ID} {--all : Apply to all streams}';
    protected $description = 'Manage streams - start, stop, restart, or check status';

    public function handle(StreamProcess $streamProcess): int
    {
        $action = $this->argument('action');
        $streamId = $this->option('stream');

        if ($streamId) {
            $stream = Stream::find($streamId);
            if (!$stream) { $this->error("Stream #{$streamId} not found."); return 1; }
            $this->processStream($stream, $action, $streamProcess);
        } elseif ($this->option('all')) {
            $streams = Stream::where('admin_enabled', 1)->get();
            $this->info("Processing {$streams->count()} streams...");
            $bar = $this->output->createProgressBar($streams->count());
            foreach ($streams as $stream) { $this->processStream($stream, $action, $streamProcess); $bar->advance(); }
            $bar->finish(); $this->newLine();
        } else {
            $this->table(['ID', 'Name', 'Status', 'PID', 'Viewers'], Stream::all()->map(fn ($s) => [
                $s->id, $s->stream_display_name, $s->status_label, $s->pid ?? '—', $s->current_viewers,
            ])->toArray());
        }

        return 0;
    }

    private function processStream(Stream $stream, string $action, StreamProcess $streamProcess): void
    {
        match ($action) {
            'start' => $this->startStream($stream, $streamProcess),
            'stop' => $this->stopStream($stream, $streamProcess),
            'restart' => $this->restartStream($stream, $streamProcess),
            'status' => $this->info("{$stream->stream_display_name}: {$stream->status_label}"),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    private function startStream(Stream $stream, StreamProcess $streamProcess): void
    {
        if ($stream->type === 'radio') {
            $this->warn("Radio streams are not started via FFmpeg: {$stream->stream_display_name}");
            return;
        }
        $ok = $streamProcess->start($stream);
        $this->info($ok ? "Started: {$stream->stream_display_name}" : "Failed to start: {$stream->stream_display_name}");
    }

    private function stopStream(Stream $stream, StreamProcess $streamProcess): void
    {
        $streamProcess->stop($stream);
        $this->info("Stopped: {$stream->stream_display_name}");
    }

    private function restartStream(Stream $stream, StreamProcess $streamProcess): void
    {
        $streamProcess->restart($stream);
        $this->info("Restarted: {$stream->stream_display_name}");
    }
}
