<?php

namespace App\Streaming\Delivery;

/**
 * Reads TS segments from disk or ramdisk.
 * Handles segment path resolution and numbering.
 */
class SegmentReader
{
    private array $searchPaths;

    public function __construct()
    {
        $this->searchPaths = [
            storage_path('app/streaming'),  // FFmpeg StreamProcess writes here
            storage_path('app/streams'),
            '/tmp/streams',
            '/dev/shm/streams',
            '/home/xc_vm/content/streams',
        ];
    }

    public function getSegmentPath(int $streamId, int|string $segmentIndex): ?string
    {
        $filename = is_numeric($segmentIndex) ? "{$segmentIndex}.ts" : $segmentIndex;
        if (!str_ends_with($filename, '.ts')) {
            $filename .= '.ts';
        }

        foreach ($this->searchPaths as $basePath) {
            $path = "{$basePath}/{$streamId}/{$filename}";
            if (file_exists($path)) {
                return $path;
            }
        }

        foreach ($this->searchPaths as $basePath) {
            $dir = "{$basePath}/{$streamId}";
            if (is_dir($dir)) {
                $pattern = "{$dir}/*{$segmentIndex}*.ts";
                $matches = glob($pattern);
                if (!empty($matches)) {
                    return $matches[0];
                }
            }
        }

        return null;
    }

    public function getPlaylistPath(int $streamId): ?string
    {
        $filenames = ['index.m3u8', 'playlist.m3u8', "{$streamId}.m3u8"];

        foreach ($this->searchPaths as $basePath) {
            foreach ($filenames as $name) {
                $path = "{$basePath}/{$streamId}/{$name}";
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    public function getLatestSegments(int $streamId, int $count = 5): array
    {
        foreach ($this->searchPaths as $basePath) {
            $dir = "{$basePath}/{$streamId}";
            if (!is_dir($dir)) {
                continue;
            }

            $files = glob("{$dir}/*.ts");
            if (empty($files)) {
                continue;
            }

            usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

            return array_slice($files, 0, $count);
        }

        return [];
    }

    public function getSegmentInfo(string $segmentPath): array
    {
        return [
            'path' => $segmentPath,
            'size' => filesize($segmentPath),
            'modified' => filemtime($segmentPath),
            'filename' => basename($segmentPath),
        ];
    }
}
