<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use Illuminate\Support\Collection;

/**
 * Monitor stream health: check if PID is running, mark dead streams, optional auto-restart.
 */
class StreamMonitor
{
    public function __construct(
        private StreamRepository $streamRepository,
        private StreamProcess $streamProcess
    ) {}

    /**
     * Check all enabled streams; mark as error if PID is dead. Optionally restart.
     *
     * @return array{checked: int, dead: int, restarted: int}
     */
    public function checkAll(bool $autoRestart = false): array
    {
        $streams = Stream::where('admin_enabled', 1)->where('status', 1)->get();
        $checked = 0;
        $dead = 0;
        $restarted = 0;

        foreach ($streams as $stream) {
            $checked++;
            $pid = $stream->pid;
            if (!$pid) {
                continue;
            }
            if (!$this->streamProcess->isPidRunning((int) $pid)) {
                $dead++;
                $stream->update(['status' => 3, 'pid' => null]);
                if ($autoRestart) {
                    $this->streamProcess->start($stream);
                    $restarted++;
                }
            }
        }

        return ['checked' => $checked, 'dead' => $dead, 'restarted' => $restarted];
    }

    /**
     * Check a single stream; return true if running.
     */
    public function check(Stream $stream): bool
    {
        $pid = $stream->pid;
        if (!$pid) {
            return false;
        }
        $running = $this->streamProcess->isPidRunning((int) $pid);
        if (!$running) {
            $stream->update(['status' => 3, 'pid' => null]);
        }
        return $running;
    }

    /**
     * Get streams that are marked online but PID is dead.
     *
     * @return Collection<int, Stream>
     */
    public function getDeadStreams(): Collection
    {
        $streams = Stream::where('admin_enabled', 1)->where('status', 1)->whereNotNull('pid')->get();
        return $streams->filter(fn (Stream $s) => !$this->streamProcess->isPidRunning((int) $s->pid));
    }
}
