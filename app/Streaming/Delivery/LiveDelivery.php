<?php

namespace App\Streaming\Delivery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves live streams — HLS playlists, TS segments, RTMP redirects.
 * Handles the primary streaming hot path.
 */
class LiveDelivery
{
    private SegmentReader $segmentReader;

    public function __construct(SegmentReader $segmentReader)
    {
        $this->segmentReader = $segmentReader;
    }

    public function serve(array $stream, array $line, Request $request): mixed
    {
        $format = $this->detectFormat($request);

        return match ($format) {
            'hls' => $this->serveHls($stream, $line, $request),
            'ts' => $this->serveTs($stream, $line, $request),
            'rtmp' => $this->serveRtmp($stream),
            default => $this->serveHls($stream, $line, $request),
        };
    }

    private function serveHls(array $stream, array $line, Request $request): Response
    {
        $streamId = $stream['id'];

        // Use the same host/port as the request so segment URLs work when opening from browser
        $segmentBase = $request->getSchemeAndHttpHost() . '/streaming/segment';
        $authParams = 'username=' . urlencode($line['username']) . '&password=' . urlencode($line['password']);

        // Prefer the live playlist that FFmpeg is writing (correct segment numbers and order)
        $playlistPath = $this->segmentReader->getPlaylistPath($streamId);
        if ($playlistPath && is_readable($playlistPath)) {
            $content = file_get_contents($playlistPath);
            if ($content !== false) {
                $rewritten = $this->rewritePlaylistSegmentUrls($content, $streamId, $segmentBase, $authParams);
                return response($rewritten, 200, [
                    'Content-Type' => 'application/vnd.apple.mpegurl',
                    'Cache-Control' => 'no-cache, no-store',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }

        // Fallback: static playlist (used when stream not started yet or no playlist file)
        $playlist = "#EXTM3U\n";
        $playlist .= "#EXT-X-VERSION:3\n";
        $playlist .= "#EXT-X-TARGETDURATION:10\n";
        $playlist .= "#EXT-X-MEDIA-SEQUENCE:0\n";
        for ($i = 0; $i < 5; $i++) {
            $playlist .= "#EXTINF:10.0,\n";
            $playlist .= "{$segmentBase}/{$streamId}/{$i}.ts?{$authParams}\n";
        }

        return response($playlist, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'no-cache, no-store',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Rewrite segment URIs in an HLS playlist to point to our segment endpoint with auth.
     */
    private function rewritePlaylistSegmentUrls(string $playlistContent, int $streamId, string $segmentBase, string $authParams): string
    {
        $lines = explode("\n", $playlistContent);
        $out = [];
        foreach ($lines as $line) {
            $line = rtrim($line, "\r");
            if ($line === '' || str_starts_with($line, '#')) {
                $out[] = $line;
                continue;
            }
            // Segment URI (relative or absolute) -> our segment URL with auth
            $segmentName = basename($line);
            if (!str_ends_with($segmentName, '.ts')) {
                $segmentName .= '.ts';
            }
            $out[] = "{$segmentBase}/{$streamId}/{$segmentName}?{$authParams}";
        }
        return implode("\n", $out);
    }

    private function serveTs(array $stream, array $line, Request $request): mixed
    {
        $segmentPath = $this->segmentReader->getSegmentPath(
            $stream['id'],
            $request->route('segment', 0)
        );

        if (!$segmentPath || !file_exists($segmentPath)) {
            return response('Segment not found', 404);
        }

        return response()->file($segmentPath, [
            'Content-Type' => 'video/MP2T',
            'Cache-Control' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    private function serveRtmp(array $stream): mixed
    {
        $serverId = $stream['server_id'] ?? 1;
        $server = DB::table('servers')->find($serverId);

        if (!$server) {
            return response('No server available', 503);
        }

        $rtmpUrl = "rtmp://{$server->server_ip}:{$server->rtmp_port}/live/{$stream['id']}";

        return response()->json([
            'url' => $rtmpUrl,
            'stream_id' => $stream['id'],
        ]);
    }

    private function detectFormat(Request $request): string
    {
        $path = $request->path();
        $output = $request->input('output', $request->input('type', ''));

        if (str_ends_with($path, '.m3u8') || $output === 'hls' || $output === 'm3u8') {
            return 'hls';
        }
        if (str_ends_with($path, '.ts') || $output === 'ts') {
            return 'ts';
        }
        if ($output === 'rtmp') {
            return 'rtmp';
        }

        return 'hls';
    }
}
