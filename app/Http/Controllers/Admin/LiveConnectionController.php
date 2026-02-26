<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Stream\Models\Stream;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveConnectionController extends Controller
{
    public function index(Request $request)
    {
        $connections = Line::where('active_connections', '>', 0)
            ->orderByDesc('active_connections')
            ->get(['id', 'username', 'active_connections', 'max_connections', 'exp_date', 'admin_enabled']);

        $activeStreams = Stream::where('status', 1)
            ->withCount([])
            ->orderByDesc('current_viewers')
            ->get(['id', 'stream_display_name', 'current_viewers', 'status', 'server_id']);

        $totalConnections = $connections->sum('active_connections');
        $totalViewers = $activeStreams->sum('current_viewers');

        return Inertia::render('Admin/Connections/Index', [
            'connections' => $connections,
            'activeStreams' => $activeStreams,
            'totalConnections' => $totalConnections,
            'totalViewers' => $totalViewers,
        ]);
    }
}
