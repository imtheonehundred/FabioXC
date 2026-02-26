<?php

namespace App\Streaming\Delivery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    private function serveHls(array $stream, array $line, Request $request): StreamedResponse
    {
        $streamId = $stream['id'];
        $serverId = $stream['server_id'] ?? 1;

        $server = DB::table('servers')->find($serverId);
        $baseUrl = $server ? ($server->domain_name ?: $server->server_ip) : 'localhost';
        $port = $server->http_port ?? 80;

        $playlist = "#EXTM3U\n";
        $playlist .= "#EXT-X-VERSION:3\n";
        $playlist .= "#EXT-X-TARGETDURATION:10\n";
        $playlist .= "#EXT-X-MEDIA-SEQUENCE:0\n";

        $segmentBase = "http://{$baseUrl}:{$port}/streaming/segment";
        $authParams = "username={$line['username']}&password={$line['password']}";

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
