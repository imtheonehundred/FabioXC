<?php

namespace App\Streaming\Delivery;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves VOD files (movies, episodes) with HTTP range support
 * for seeking and progressive download.
 */
class VodDelivery
{
    public function serve(array $vod, array $line, Request $request): mixed
    {
        $source = $vod['stream_source'] ?? '';
        $extension = $vod['container_extension'] ?? 'mp4';

        if ($this->isRemoteSource($source)) {
            return $this->serveRedirect($source);
        }

        if (file_exists($source)) {
            return $this->serveFile($source, $extension, $request);
        }

        $basePath = storage_path('app/vod');
        $localPath = "{$basePath}/{$vod['id']}.{$extension}";

        if (file_exists($localPath)) {
            return $this->serveFile($localPath, $extension, $request);
        }

        return response('File not found', 404);
    }

    private function serveRedirect(string $url): mixed
    {
        return redirect($url);
    }

    private function serveFile(string $path, string $extension, Request $request): mixed
    {
        $fileSize = filesize($path);
        $mimeType = $this->getMimeType($extension);

        $rangeHeader = $request->header('Range');
        if ($rangeHeader) {
            return $this->serveRange($path, $fileSize, $mimeType, $rangeHeader);
        }

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function serveRange(string $path, int $fileSize, string $mimeType, string $rangeHeader): StreamedResponse
    {
        preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches);
        $start = (int) ($matches[1] ?? 0);
        $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;

        $end = min($end, $fileSize - 1);
        $length = $end - $start + 1;

        return response()->stream(function () use ($path, $start, $length) {
            $fp = fopen($path, 'rb');
            fseek($fp, $start);
            $remaining = $length;
            while ($remaining > 0 && !feof($fp)) {
                $chunk = min(8192, $remaining);
                echo fread($fp, $chunk);
                $remaining -= $chunk;
                flush();
            }
            fclose($fp);
        }, 206, [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function isRemoteSource(string $source): bool
    {
        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://');
    }

    private function getMimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'mp4', 'm4v' => 'video/mp4',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            'ts' => 'video/MP2T',
            'flv' => 'video/x-flv',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            default => 'application/octet-stream',
        };
    }
}
