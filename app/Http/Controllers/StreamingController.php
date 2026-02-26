<?php

namespace App\Http\Controllers;

use App\Streaming\Auth\TokenAuth;
use App\Streaming\Auth\StreamAuth;
use App\Streaming\Auth\DeviceLock;
use App\Streaming\Balancer\LoadBalancer;
use App\Streaming\Delivery\LiveDelivery;
use App\Streaming\Delivery\VodDelivery;
use App\Streaming\Delivery\TimeshiftDelivery;
use App\Streaming\Protection\ConnectionLimiter;
use App\Streaming\Protection\GeoBlock;
use App\Streaming\Protection\RestreamDetector;
use App\Streaming\Health\BitrateTracker;
use App\Streaming\StreamingBootstrap;
use Illuminate\Http\Request;

/**
 * Entry point for all streaming requests.
 * Orchestrates: auth -> protection -> load balance -> delivery.
 * Bypasses web middleware (no sessions, CSRF, or Inertia).
 */
class StreamingController extends Controller
{
    public function __construct(
        private TokenAuth $tokenAuth,
        private StreamAuth $streamAuth,
        private DeviceLock $deviceLock,
        private ConnectionLimiter $connectionLimiter,
        private GeoBlock $geoBlock,
        private RestreamDetector $restreamDetector,
        private LoadBalancer $loadBalancer,
        private LiveDelivery $liveDelivery,
        private VodDelivery $vodDelivery,
        private TimeshiftDelivery $timeshiftDelivery,
        private BitrateTracker $bitrateTracker,
    ) {}

    public function live(Request $request): mixed
    {
        StreamingBootstrap::boot();

        $lineData = $this->tokenAuth->parse($request);
        if (!$lineData) {
            return $this->deny('Authentication failed', 401);
        }

        $streamId = $this->tokenAuth->parseStreamId($request);
        if (!$streamId) {
            return $this->deny('Stream ID required', 400);
        }

        $clientIp = $request->ip();
        $userAgent = $request->userAgent() ?? '';

        $geoResult = $this->geoBlock->check($clientIp, $userAgent);
        if (!$geoResult['allowed']) {
            return $this->deny($geoResult['reason'], 403);
        }

        $authResult = $this->streamAuth->validate($lineData, $streamId, $clientIp, $userAgent);
        if (!$authResult['allowed']) {
            return $this->deny($authResult['reason'], 403);
        }

        $deviceResult = $this->deviceLock->check($lineData['line_id'], $clientIp, $userAgent);
        if (!$deviceResult['allowed']) {
            return $this->deny($deviceResult['reason'], 403);
        }

        $connectionId = $this->connectionLimiter->generateConnectionId($clientIp, $userAgent, $streamId);
        $connResult = $this->connectionLimiter->check($lineData['line_id'], $lineData['max_connections'], $connectionId);
        if (!$connResult['allowed']) {
            return $this->deny($connResult['reason'], 429);
        }

        $this->restreamDetector->check($lineData['line_id'], $clientIp, $userAgent);

        $server = $this->loadBalancer->selectServer($streamId, $authResult['stream']['server_id'] ?? null);
        if (!$server) {
            return $this->deny('No available servers', 503);
        }

        return $this->liveDelivery->serve($authResult['stream'], $lineData, $request);
    }

    public function vod(Request $request): mixed
    {
        StreamingBootstrap::boot();

        $lineData = $this->tokenAuth->parse($request);
        if (!$lineData) {
            return $this->deny('Authentication failed', 401);
        }

        $vodId = $request->route('vod_id') ?? $request->input('vod_id');
        if (!$vodId) {
            return $this->deny('VOD ID required', 400);
        }

        $clientIp = $request->ip();
        $userAgent = $request->userAgent() ?? '';

        $geoResult = $this->geoBlock->check($clientIp, $userAgent);
        if (!$geoResult['allowed']) {
            return $this->deny($geoResult['reason'], 403);
        }

        $authResult = $this->streamAuth->validateVod($lineData, (int) $vodId, 'movie', $clientIp, $userAgent);
        if (!$authResult['allowed']) {
            return $this->deny($authResult['reason'], 403);
        }

        $connectionId = $this->connectionLimiter->generateConnectionId($clientIp, $userAgent, (int) $vodId);
        $connResult = $this->connectionLimiter->check($lineData['line_id'], $lineData['max_connections'], $connectionId);
        if (!$connResult['allowed']) {
            return $this->deny($connResult['reason'], 429);
        }

        return $this->vodDelivery->serve($authResult['vod'], $lineData, $request);
    }

    public function series(Request $request): mixed
    {
        StreamingBootstrap::boot();

        $lineData = $this->tokenAuth->parse($request);
        if (!$lineData) {
            return $this->deny('Authentication failed', 401);
        }

        $episodeId = $request->route('episode_id') ?? $request->input('episode_id');
        if (!$episodeId) {
            return $this->deny('Episode ID required', 400);
        }

        $clientIp = $request->ip();
        $userAgent = $request->userAgent() ?? '';

        $authResult = $this->streamAuth->validateVod($lineData, (int) $episodeId, 'series', $clientIp, $userAgent);
        if (!$authResult['allowed']) {
            return $this->deny($authResult['reason'], 403);
        }

        $connectionId = $this->connectionLimiter->generateConnectionId($clientIp, $userAgent, (int) $episodeId);
        $connResult = $this->connectionLimiter->check($lineData['line_id'], $lineData['max_connections'], $connectionId);
        if (!$connResult['allowed']) {
            return $this->deny($connResult['reason'], 429);
        }

        return $this->vodDelivery->serve($authResult['vod'], $lineData, $request);
    }

    public function timeshift(Request $request): mixed
    {
        StreamingBootstrap::boot();

        $lineData = $this->tokenAuth->parse($request);
        if (!$lineData) {
            return $this->deny('Authentication failed', 401);
        }

        $streamId = $this->tokenAuth->parseStreamId($request);
        if (!$streamId) {
            return $this->deny('Stream ID required', 400);
        }

        $clientIp = $request->ip();
        $userAgent = $request->userAgent() ?? '';

        $authResult = $this->streamAuth->validate($lineData, $streamId, $clientIp, $userAgent);
        if (!$authResult['allowed']) {
            return $this->deny($authResult['reason'], 403);
        }

        return $this->timeshiftDelivery->serve($authResult['stream'], $lineData, $request);
    }

    public function segment(Request $request, int $streamId, string $segment): mixed
    {
        StreamingBootstrap::boot();

        $lineData = $this->tokenAuth->parse($request);
        if (!$lineData) {
            return $this->deny('Authentication failed', 401);
        }

        $segmentReader = app(\App\Streaming\Delivery\SegmentReader::class);
        $segmentPath = $segmentReader->getSegmentPath($streamId, $segment);

        if (!$segmentPath) {
            return response('Segment not found', 404);
        }

        $this->connectionLimiter->heartbeat(
            $lineData['line_id'],
            $this->connectionLimiter->generateConnectionId($request->ip(), $request->userAgent() ?? '', $streamId)
        );

        return response()->file($segmentPath, [
            'Content-Type' => 'video/MP2T',
            'Cache-Control' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function hlsPlaylist(Request $request, int $streamId): mixed
    {
        $segmentReader = app(\App\Streaming\Delivery\SegmentReader::class);
        $playlistPath = $segmentReader->getPlaylistPath($streamId);

        if ($playlistPath && file_exists($playlistPath)) {
            return response()->file($playlistPath, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'no-cache',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        return $this->live($request);
    }

    private function deny(string $reason, int $status = 403): mixed
    {
        return response()->json([
            'error' => $reason,
            'status' => $status,
        ], $status);
    }
}
