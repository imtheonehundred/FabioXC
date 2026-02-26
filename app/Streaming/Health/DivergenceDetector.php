<?php

namespace App\Streaming\Health;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Compares expected vs actual stream bitrate/quality.
 * Alerts when divergence exceeds thresholds — indicates
 * source issues, transcoding problems, or network degradation.
 */
class DivergenceDetector
{
    private const BITRATE_THRESHOLD_PERCENT = 30;
    private const FPS_THRESHOLD = 5;
    private const MIN_SAMPLES = 5;

    private BitrateTracker $tracker;

    public function __construct(BitrateTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function check(int $streamId): array
    {
        $stream = DB::table('streams')->where('id', $streamId)->first();
        if (!$stream) {
            return ['status' => 'unknown', 'reason' => 'Stream not found'];
        }

        $currentData = $this->tracker->getCurrent($streamId);
        if (!$currentData) {
            return [
                'status' => 'no_data',
                'stream_id' => $streamId,
                'reason' => 'No bitrate data available',
            ];
        }

        $avgBitrate = $this->tracker->getAverageBitrate($streamId);
        $issues = [];

        if ($stream->bitrate && $avgBitrate) {
            $expectedBitrate = $stream->bitrate * 1000;
            $deviation = abs($avgBitrate - $expectedBitrate) / max(1, $expectedBitrate) * 100;

            if ($deviation > self::BITRATE_THRESHOLD_PERCENT) {
                $issues[] = [
                    'type' => 'bitrate_divergence',
                    'expected' => $expectedBitrate,
                    'actual' => $avgBitrate,
                    'deviation_percent' => round($deviation, 1),
                ];
            }
        }

        if (isset($currentData['fps']) && $currentData['fps'] !== null) {
            $expectedFps = $this->getExpectedFps($stream);
            if ($expectedFps && abs($currentData['fps'] - $expectedFps) > self::FPS_THRESHOLD) {
                $issues[] = [
                    'type' => 'fps_divergence',
                    'expected' => $expectedFps,
                    'actual' => $currentData['fps'],
                ];
            }
        }

        if ($avgBitrate !== null && $avgBitrate < 100) {
            $issues[] = [
                'type' => 'critically_low_bitrate',
                'bitrate' => $avgBitrate,
            ];
        }

        $status = empty($issues) ? 'healthy' : (count($issues) > 1 ? 'critical' : 'warning');

        if ($status !== 'healthy') {
            Log::warning("Stream {$streamId} divergence detected", [
                'stream_id' => $streamId,
                'status' => $status,
                'issues' => $issues,
            ]);
        }

        return [
            'status' => $status,
            'stream_id' => $streamId,
            'current_bitrate' => $currentData['bitrate'] ?? null,
            'average_bitrate' => $avgBitrate,
            'current_fps' => $currentData['fps'] ?? null,
            'expected_bitrate' => $stream->bitrate ? $stream->bitrate * 1000 : null,
            'issues' => $issues,
            'checked_at' => time(),
        ];
    }

    public function checkAll(): array
    {
        $allBitrates = $this->tracker->getAllCurrentBitrates();
        $results = [];

        foreach ($allBitrates as $streamId => $data) {
            $result = $this->check($streamId);
            if ($result['status'] !== 'healthy') {
                $results[] = $result;
            }
        }

        return [
            'total_streams_checked' => count($allBitrates),
            'issues_found' => count($results),
            'streams_with_issues' => $results,
        ];
    }

    private function getExpectedFps(object $stream): ?float
    {
        if (isset($stream->resolution)) {
            return match ($stream->resolution) {
                '720p', '1080p' => 30.0,
                '4K' => 30.0,
                '480p' => 25.0,
                default => null,
            };
        }
        return null;
    }
}
