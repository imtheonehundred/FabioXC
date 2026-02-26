<?php

namespace App\Streaming\Codec;

/**
 * Parses MPEG-TS packet headers to extract PID, bitrate, and codec info.
 * Used for stream analysis and quality monitoring.
 */
class TsParser
{
    private const SYNC_BYTE = 0x47;
    private const PACKET_SIZE = 188;
    private const PAT_PID = 0x0000;
    private const SDT_PID = 0x0011;

    public function parseFile(string $path, int $maxPackets = 1000): array
    {
        if (!file_exists($path)) {
            return ['error' => 'File not found'];
        }

        $fp = fopen($path, 'rb');
        if (!$fp) {
            return ['error' => 'Cannot open file'];
        }

        $pids = [];
        $packetCount = 0;
        $totalBytes = 0;

        while (!feof($fp) && $packetCount < $maxPackets) {
            $packet = fread($fp, self::PACKET_SIZE);
            if (strlen($packet) < self::PACKET_SIZE) break;

            if (ord($packet[0]) !== self::SYNC_BYTE) {
                $this->resync($fp);
                continue;
            }

            $header = $this->parsePacketHeader($packet);
            $pid = $header['pid'];

            if (!isset($pids[$pid])) {
                $pids[$pid] = [
                    'pid' => $pid,
                    'packets' => 0,
                    'type' => $this->classifyPid($pid),
                    'has_payload' => false,
                    'has_adaptation' => false,
                ];
            }

            $pids[$pid]['packets']++;
            $pids[$pid]['has_payload'] = $pids[$pid]['has_payload'] || $header['has_payload'];
            $pids[$pid]['has_adaptation'] = $pids[$pid]['has_adaptation'] || $header['has_adaptation'];

            $packetCount++;
            $totalBytes += self::PACKET_SIZE;
        }

        fclose($fp);

        $fileSize = filesize($path);

        return [
            'file_size' => $fileSize,
            'packets_analyzed' => $packetCount,
            'total_pids' => count($pids),
            'estimated_bitrate' => $this->estimateBitrate($fileSize, $packetCount),
            'pids' => array_values($pids),
        ];
    }

    public function parsePacketHeader(string $packet): array
    {
        $byte1 = ord($packet[1]);
        $byte2 = ord($packet[2]);
        $byte3 = ord($packet[3]);

        return [
            'sync' => ord($packet[0]),
            'tei' => ($byte1 >> 7) & 1,
            'pusi' => ($byte1 >> 6) & 1,
            'priority' => ($byte1 >> 5) & 1,
            'pid' => (($byte1 & 0x1F) << 8) | $byte2,
            'scrambling' => ($byte3 >> 6) & 3,
            'has_adaptation' => (bool) (($byte3 >> 5) & 1),
            'has_payload' => (bool) (($byte3 >> 4) & 1),
            'continuity' => $byte3 & 0x0F,
        ];
    }

    public function detectCodec(string $path): array
    {
        $fp = fopen($path, 'rb');
        if (!$fp) return [];

        $codecs = [];
        $packetsChecked = 0;

        while (!feof($fp) && $packetsChecked < 500) {
            $packet = fread($fp, self::PACKET_SIZE);
            if (strlen($packet) < self::PACKET_SIZE) break;
            if (ord($packet[0]) !== self::SYNC_BYTE) continue;

            $header = $this->parsePacketHeader($packet);

            if ($header['pusi'] && $header['has_payload'] && $header['pid'] > 0x1F) {
                $payloadOffset = 4 + ($header['has_adaptation'] ? (ord($packet[4]) + 1) : 0);
                if ($payloadOffset < self::PACKET_SIZE - 4) {
                    $streamType = $this->detectStreamType(substr($packet, $payloadOffset, 20));
                    if ($streamType) {
                        $codecs[$header['pid']] = $streamType;
                    }
                }
            }
            $packetsChecked++;
        }

        fclose($fp);
        return $codecs;
    }

    private function resync($fp): void
    {
        while (!feof($fp)) {
            $byte = fread($fp, 1);
            if (strlen($byte) === 0) return;
            if (ord($byte) === self::SYNC_BYTE) {
                fseek($fp, -1, SEEK_CUR);
                return;
            }
        }
    }

    private function classifyPid(int $pid): string
    {
        return match (true) {
            $pid === self::PAT_PID => 'PAT',
            $pid === 0x0001 => 'CAT',
            $pid === self::SDT_PID => 'SDT',
            $pid >= 0x0020 && $pid <= 0x1FFA => 'stream',
            $pid === 0x1FFF => 'null',
            default => 'other',
        };
    }

    private function detectStreamType(string $payload): ?string
    {
        if (strlen($payload) < 4) return null;

        $startCode = (ord($payload[0]) << 16) | (ord($payload[1]) << 8) | ord($payload[2]);
        if ($startCode === 0x000001) {
            $streamId = ord($payload[3]);
            if ($streamId >= 0xE0 && $streamId <= 0xEF) return 'video';
            if ($streamId >= 0xC0 && $streamId <= 0xDF) return 'audio';
        }

        return null;
    }

    private function estimateBitrate(int $fileSize, int $packetCount): int
    {
        if ($packetCount === 0) return 0;
        $totalPacketsEstimate = (int) ($fileSize / self::PACKET_SIZE);
        $durationEstimate = max(1, $totalPacketsEstimate / 7000);
        return (int) (($fileSize * 8) / $durationEstimate);
    }
}
