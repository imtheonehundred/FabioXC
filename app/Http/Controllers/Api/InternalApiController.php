<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Server\Models\Server;
use App\Domain\Stream\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalApiController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cpu_usage' => 'nullable|numeric|min:0|max:100',
            'mem_usage' => 'nullable|numeric|min:0|max:100',
            'disk_usage' => 'nullable|numeric|min:0|max:100',
            'total_clients' => 'nullable|integer|min:0',
            'uptime' => 'nullable|string',
            'network_rx' => 'nullable|integer',
            'network_tx' => 'nullable|integer',
        ]);

        $server = $request->input('api_server');
        $server->update(array_merge($data, [
            'status' => 1,
            'last_check_at' => now(),
        ]));

        return response()->json([
            'status' => 'ok',
            'server_id' => $server->id,
            'timestamp' => now()->timestamp,
        ]);
    }

    public function getStreamConfig(Request $request, int $id): JsonResponse
    {
        $stream = Stream::with(['category'])->find($id);

        if (!$stream) {
            return response()->json(['error' => 'Stream not found'], 404);
        }

        return response()->json([
            'id' => $stream->id,
            'stream_display_name' => $stream->stream_display_name,
            'stream_source' => $stream->stream_source,
            'type' => $stream->type,
            'status' => $stream->status,
            'category_id' => $stream->category_id,
            'category_name' => $stream->category?->category_name,
            'custom_ffmpeg' => $stream->custom_ffmpeg,
            'transcode' => $stream->transcode,
            'admin_enabled' => $stream->admin_enabled,
            'stream_icon' => $stream->stream_icon,
        ]);
    }

    public function validateLine(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            return response()->json(['valid' => false, 'reason' => 'Missing credentials'], 400);
        }

        $line = Line::where('username', $username)->where('password', $password)->first();

        if (!$line) {
            return response()->json(['valid' => false, 'reason' => 'Invalid credentials']);
        }

        if (!$line->admin_enabled) {
            return response()->json(['valid' => false, 'reason' => 'Account disabled']);
        }

        if ($line->exp_date && $line->exp_date->isPast()) {
            return response()->json(['valid' => false, 'reason' => 'Account expired']);
        }

        if ($line->active_connections >= $line->max_connections) {
            return response()->json(['valid' => false, 'reason' => 'Max connections reached']);
        }

        return response()->json([
            'valid' => true,
            'line_id' => $line->id,
            'username' => $line->username,
            'max_connections' => $line->max_connections,
            'active_connections' => $line->active_connections,
            'exp_date' => $line->exp_date?->timestamp,
            'bouquet' => $line->bouquet,
            'is_restreamer' => (int) $line->is_restreamer,
            'allowed_ips' => $line->allowed_ips,
            'allowed_ua' => $line->allowed_ua,
        ]);
    }

    public function updateConnections(Request $request): JsonResponse
    {
        $data = $request->validate([
            'connections' => 'required|array',
            'connections.*.line_id' => 'required|integer|exists:lines,id',
            'connections.*.count' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['connections'] as $conn) {
                Line::where('id', $conn['line_id'])->update([
                    'active_connections' => $conn['count'],
                ]);
            }
        });

        return response()->json([
            'status' => 'ok',
            'updated' => count($data['connections']),
        ]);
    }

    public function getServerConfig(Request $request): JsonResponse
    {
        $server = $request->input('api_server');
        $settings = DB::table('settings')->pluck('value', 'key');

        $streamCount = Stream::where('server_id', $server->id)->where('admin_enabled', 1)->count();

        return response()->json([
            'server' => [
                'id' => $server->id,
                'server_name' => $server->server_name,
                'server_ip' => $server->server_ip,
                'http_port' => $server->http_port,
                'rtmp_port' => $server->rtmp_port,
            ],
            'assigned_streams' => $streamCount,
            'settings' => [
                'timezone' => $settings['timezone'] ?? 'UTC',
                'auto_restart_streams' => (bool) ($settings['auto_restart_streams'] ?? true),
                'max_connections_per_line' => (int) ($settings['max_connections_per_line'] ?? 1),
            ],
        ]);
    }
}
