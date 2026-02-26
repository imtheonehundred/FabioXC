<?php

namespace App\Streaming\Codec;

/**
 * Builds FFmpeg command lines for transcoding, remuxing, and thumbnailing.
 * Extracted from CoreUtilities FFmpeg methods.
 */
class FFmpegCommand
{
    private string $ffmpegBin;
    private string $ffprobeBin;

    public function __construct(?string $ffmpegBin = null, ?string $ffprobeBin = null)
    {
        $this->ffmpegBin = $ffmpegBin ?? '/usr/bin/ffmpeg';
        $this->ffprobeBin = $ffprobeBin ?? '/usr/bin/ffprobe';
    }

    public function buildLiveTranscode(string $source, string $outputDir, TranscodeProfile $profile, array $options = []): string
    {
        $args = [$this->ffmpegBin];

        // Input options
        $args[] = '-re';
        if (isset($options['timeout'])) {
            $args[] = "-timeout {$options['timeout']}";
        }
        if ($this->isHttpSource($source)) {
            $userAgent = config('streaming.outbound_user_agent', 'IPTV/1.0 (compatible; set-top player)');
            $args[] = '-user_agent ' . $this->escapeShellArg($userAgent);
            $headers = config('streaming.outbound_headers', []);
            if (is_array($headers) && $headers !== []) {
                $headersStr = implode("\r\n", $headers);
                $args[] = '-headers ' . $this->escapeShellArg($headersStr);
            }
        }
        $args[] = "-i \"{$source}\"";

        // Video codec
        if ($profile->videoCodec === 'copy') {
            $args[] = '-c:v copy';
        } else {
            $args[] = "-c:v {$profile->videoCodec}";
            if ($profile->videoBitrate) {
                $args[] = "-b:v {$profile->videoBitrate}";
            }
            if ($profile->resolution) {
                $args[] = "-s {$profile->resolution}";
            }
            if ($profile->fps) {
                $args[] = "-r {$profile->fps}";
            }
            if ($profile->preset) {
                $args[] = "-preset {$profile->preset}";
            }
        }

        // Audio codec
        if ($profile->audioCodec === 'copy') {
            $args[] = '-c:a copy';
        } else {
            $args[] = "-c:a {$profile->audioCodec}";
            if ($profile->audioBitrate) {
                $args[] = "-b:a {$profile->audioBitrate}";
            }
            if ($profile->audioSampleRate) {
                $args[] = "-ar {$profile->audioSampleRate}";
            }
            if ($profile->audioChannels) {
                $args[] = "-ac {$profile->audioChannels}";
            }
        }

        // HLS output
        $segmentDuration = $options['segment_duration'] ?? 10;
        $args[] = '-f hls';
        $args[] = "-hls_time {$segmentDuration}";
        $args[] = '-hls_list_size 5';
        $args[] = '-hls_flags delete_segments+append_list';
        $args[] = "-hls_segment_filename \"{$outputDir}/%d.ts\"";
        $args[] = "\"{$outputDir}/index.m3u8\"";

        return implode(' ', $args);
    }

    public function buildVodTranscode(string $source, string $output, TranscodeProfile $profile): string
    {
        $args = [$this->ffmpegBin];
        $args[] = '-y';
        $args[] = "-i \"{$source}\"";

        if ($profile->videoCodec === 'copy') {
            $args[] = '-c:v copy';
        } else {
            $args[] = "-c:v {$profile->videoCodec}";
            if ($profile->videoBitrate) $args[] = "-b:v {$profile->videoBitrate}";
            if ($profile->resolution) $args[] = "-s {$profile->resolution}";
        }

        if ($profile->audioCodec === 'copy') {
            $args[] = '-c:a copy';
        } else {
            $args[] = "-c:a {$profile->audioCodec}";
            if ($profile->audioBitrate) $args[] = "-b:a {$profile->audioBitrate}";
        }

        $args[] = '-movflags +faststart';
        $args[] = "\"{$output}\"";

        return implode(' ', $args);
    }

    public function buildThumbnail(string $source, string $output, int $atSecond = 5): string
    {
        return implode(' ', [
            $this->ffmpegBin,
            '-y',
            "-ss {$atSecond}",
            "-i \"{$source}\"",
            '-vframes 1',
            '-q:v 2',
            '-s 320x180',
            "\"{$output}\"",
        ]);
    }

    public function buildProbe(string $source): string
    {
        return implode(' ', [
            $this->ffprobeBin,
            '-v quiet',
            '-print_format json',
            '-show_format',
            '-show_streams',
            "\"{$source}\"",
        ]);
    }

    public function parseProbeOutput(string $json): ?array
    {
        $data = json_decode($json, true);
        if (!$data) return null;

        $result = [
            'duration' => $data['format']['duration'] ?? null,
            'bitrate' => isset($data['format']['bit_rate']) ? (int) $data['format']['bit_rate'] : null,
            'format' => $data['format']['format_name'] ?? null,
            'streams' => [],
        ];

        foreach ($data['streams'] ?? [] as $stream) {
            $info = [
                'index' => $stream['index'] ?? 0,
                'codec_type' => $stream['codec_type'] ?? 'unknown',
                'codec_name' => $stream['codec_name'] ?? 'unknown',
            ];

            if ($stream['codec_type'] === 'video') {
                $info['width'] = $stream['width'] ?? 0;
                $info['height'] = $stream['height'] ?? 0;
                $info['fps'] = $this->parseFps($stream['r_frame_rate'] ?? '0/1');
                $info['bitrate'] = isset($stream['bit_rate']) ? (int) $stream['bit_rate'] : null;
            }

            if ($stream['codec_type'] === 'audio') {
                $info['sample_rate'] = $stream['sample_rate'] ?? null;
                $info['channels'] = $stream['channels'] ?? null;
                $info['bitrate'] = isset($stream['bit_rate']) ? (int) $stream['bit_rate'] : null;
            }

            $result['streams'][] = $info;
        }

        return $result;
    }

    private function parseFps(string $rational): float
    {
        $parts = explode('/', $rational);
        if (count($parts) === 2 && (int) $parts[1] > 0) {
            return round((int) $parts[0] / (int) $parts[1], 2);
        }
        return (float) $rational;
    }

    private function isHttpSource(string $source): bool
    {
        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://');
    }

    private function escapeShellArg(string $value): string
    {
        return '"' . addcslashes($value, '"\\') . '"';
    }
}
