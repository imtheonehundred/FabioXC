<?php

namespace App\Streaming\Codec;

/**
 * Quality presets for transcoding operations.
 * Defines codec, bitrate, resolution, and encoding parameters.
 */
class TranscodeProfile
{
    public function __construct(
        public readonly string $name,
        public readonly string $videoCodec = 'copy',
        public readonly string $audioCodec = 'copy',
        public readonly ?string $videoBitrate = null,
        public readonly ?string $audioBitrate = null,
        public readonly ?string $resolution = null,
        public readonly ?int $fps = null,
        public readonly ?string $preset = null,
        public readonly ?string $audioSampleRate = null,
        public readonly ?int $audioChannels = null,
    ) {}

    public static function passthrough(): self
    {
        return new self(name: 'passthrough');
    }

    public static function h264_720p(): self
    {
        return new self(
            name: '720p',
            videoCodec: 'libx264',
            audioCodec: 'aac',
            videoBitrate: '2500k',
            audioBitrate: '128k',
            resolution: '1280x720',
            fps: 30,
            preset: 'veryfast',
            audioSampleRate: '44100',
            audioChannels: 2,
        );
    }

    public static function h264_1080p(): self
    {
        return new self(
            name: '1080p',
            videoCodec: 'libx264',
            audioCodec: 'aac',
            videoBitrate: '5000k',
            audioBitrate: '192k',
            resolution: '1920x1080',
            fps: 30,
            preset: 'veryfast',
            audioSampleRate: '48000',
            audioChannels: 2,
        );
    }

    public static function h264_480p(): self
    {
        return new self(
            name: '480p',
            videoCodec: 'libx264',
            audioCodec: 'aac',
            videoBitrate: '1000k',
            audioBitrate: '96k',
            resolution: '854x480',
            fps: 25,
            preset: 'veryfast',
            audioSampleRate: '44100',
            audioChannels: 2,
        );
    }

    public static function audioOnly(): self
    {
        return new self(
            name: 'audio_only',
            videoCodec: 'copy',
            audioCodec: 'aac',
            audioBitrate: '128k',
            audioSampleRate: '44100',
            audioChannels: 2,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? 'custom',
            videoCodec: $data['video_codec'] ?? 'copy',
            audioCodec: $data['audio_codec'] ?? 'copy',
            videoBitrate: $data['video_bitrate'] ?? null,
            audioBitrate: $data['audio_bitrate'] ?? null,
            resolution: $data['resolution'] ?? null,
            fps: isset($data['fps']) ? (int) $data['fps'] : null,
            preset: $data['preset'] ?? null,
            audioSampleRate: $data['audio_sample_rate'] ?? null,
            audioChannels: isset($data['audio_channels']) ? (int) $data['audio_channels'] : null,
        );
    }

    public function isPassthrough(): bool
    {
        return $this->videoCodec === 'copy' && $this->audioCodec === 'copy';
    }
}
