<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use App\Streaming\Codec\FFmpegCommand;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Start/stop stream processes (FFmpeg, proxy). Manages PID and status on Stream model.
 */
class StreamProcess
{
    public function __construct(
        private StreamRepository $streamRepository,
        private FFmpegCommand $ffmpegCommand
    ) {}

    /**
     * Start a live stream (FFmpeg). Updates stream pid and status.
     * Caller must ensure output directory exists.
     */
    public function start(Stream $stream): bool
    {
        // Clear stuck "Starting" from a previous failed start (e.g. exception after setting status 2)
        if ((int) $stream->status === 2) {
            $pid = $stream->pid ? (int) $stream->pid : null;
            if (!$pid || !$this->isPidRunning($pid)) {
                $stream->update(['status' => 3, 'pid' => null]);
            }
        }

        $stream->update(['status' => 2, 'started_at' => now()]); // 2 = Starting

        $source = $stream->stream_source ?? '';
        $outputDir = storage_path('app/streaming/' . $stream->id);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        try {
            $profile = \App\Streaming\Codec\TranscodeProfile::passthrough();
            $cmd = $this->ffmpegCommand->buildLiveTranscode($source, $outputDir, $profile);
            $process = Process::fromShellCommandline($cmd);
            $process->setTimeout(null);
            $process->start();

            $pid = $process->getPid();
            $stream->update([
                'pid' => $pid,
                'status' => $pid ? 1 : 3, // 1 = Online, 3 = Error
            ]);

            if (!$pid) {
                Log::warning("StreamProcess: failed to start stream {$stream->id} (no PID)");
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::error("StreamProcess: start failed for stream {$stream->id}", ['message' => $e->getMessage()]);
            $stream->update(['status' => 3, 'pid' => null]);
            return false;
        }
    }

    /**
     * Stop stream process by PID.
     */
    public function stop(Stream $stream): bool
    {
        $stream->update(['status' => 4]); // 4 = Stopping

        $pid = $stream->pid;
        if ($pid && function_exists('posix_kill')) {
            @posix_kill((int) $pid, SIGTERM);
        }

        $stream->update(['pid' => null, 'status' => 0]); // 0 = Offline
        return true;
    }

    /**
     * Restart stream (stop then start).
     */
    public function restart(Stream $stream): bool
    {
        $this->stop($stream);
        usleep(500000); // 0.5s
        return $this->start($stream);
    }

    /**
     * Check if process with given PID is running.
     */
    public function isPidRunning(?int $pid): bool
    {
        if (!$pid || !function_exists('posix_kill')) {
            return false;
        }
        return @posix_kill($pid, 0);
    }
}
