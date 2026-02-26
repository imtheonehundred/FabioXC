<?php

namespace App\Streaming\Delivery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Serves timeshift/catchup content — allows viewers to watch
 * past live stream content within a configured archive window.
 */
class TimeshiftDelivery
{
    private SegmentReader $segmentReader;

    public function __construct(SegmentReader $segmentReader)
    {
        $this->segmentReader = $segmentReader;
    }

    public function serve(array $stream, array $line, Request $request): mixed
    {
        $streamId = $stream['id'];
        $duration = $request->input('duration', 3600);
        $start = $request->input('start');

        if (!$start) {
            return response('Missing start parameter', 400);
        }

        $startTime = is_numeric($start) ? (int) $start : strtotime($start);
        if (!$startTime) {
            return response('Invalid start time', 400);
        }

        $archivePath = $this->getArchivePath($streamId);

        if (!$archivePath || !is_dir($archivePath)) {
            return response('Timeshift archive not available for this stream', 404);
        }

        $segmentDuration = 10;
        $startSegment = (int) floor(($startTime % 86400) / $segmentDuration);
        $segmentCount = (int) ceil($duration / $segmentDuration);

        $playlist = "#EXTM3U\n";
        $playlist .= "#EXT-X-VERSION:3\n";
        $playlist .= "#EXT-X-TARGETDURATION:{$segmentDuration}\n";
        $playlist .= "#EXT-X-MEDIA-SEQUENCE:{$startSegment}\n";
        $playlist .= "#EXT-X-PLAYLIST-TYPE:EVENT\n";

        $serverId = $stream['server_id'] ?? 1;
        $server = DB::table('servers')->find($serverId);
        $baseUrl = $server ? ($server->domain_name ?: $server->server_ip) : 'localhost';
        $port = $server->http_port ?? 80;
        $authParams = "username={$line['username']}&password={$line['password']}";

        for ($i = $startSegment; $i < $startSegment + $segmentCount; $i++) {
            $segmentFile = "{$archivePath}/{$i}.ts";
            if (file_exists($segmentFile)) {
                $playlist .= "#EXTINF:{$segmentDuration}.0,\n";
                $playlist .= "http://{$baseUrl}:{$port}/streaming/timeshift/{$streamId}/{$i}.ts?{$authParams}\n";
            }
        }

        $playlist .= "#EXT-X-ENDLIST\n";

        return response($playlist, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function serveSegment(int $streamId, int $segmentIndex): mixed
    {
        $archivePath = $this->getArchivePath($streamId);
        $segmentFile = "{$archivePath}/{$segmentIndex}.ts";

        if (!file_exists($segmentFile)) {
            return response('Segment not found', 404);
        }

        return response()->file($segmentFile, [
            'Content-Type' => 'video/MP2T',
            'Cache-Control' => 'public, max-age=86400',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    private function getArchivePath(int $streamId): ?string
    {
        $basePaths = [
            storage_path("app/archive/{$streamId}"),
            "/home/xc_vm/content/archive/{$streamId}",
        ];

        foreach ($basePaths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }
}
