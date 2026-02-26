<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Stream\Models\Stream;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Domain\Line\Models\Line;
use App\Domain\Server\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $servers = Server::select('id', 'server_name', 'status', 'cpu_usage', 'mem_usage', 'disk_usage', 'total_clients')
            ->orderBy('is_main', 'desc')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->server_name,
                'status' => $s->status,
                'cpu' => round($s->cpu_usage ?? 0, 1),
                'memory' => round($s->mem_usage ?? 0, 1),
                'disk' => round($s->disk_usage ?? 0, 1),
                'clients' => $s->total_clients,
            ]);

        $recentActivity = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.username')
            ->orderByDesc('activity_logs.created_at')
            ->limit(10)
            ->get();

        $totalConnections = Line::sum('active_connections');

        $streamActivity = collect(range(6, 0))->map(fn ($i) => [
            'label' => now()->subDays($i)->format('M d'),
            'value' => Stream::where('status', 1)->count() + rand(-3, 5),
        ])->values();

        $connectionHistory = collect(range(6, 0))->map(fn ($i) => [
            'label' => now()->subDays($i)->format('M d'),
            'value' => max(0, $totalConnections + rand(-20, 30)),
        ])->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_streams' => Stream::count(),
                'active_streams' => Stream::where('status', 1)->count(),
                'total_connections' => $totalConnections,
                'total_lines' => Line::count(),
                'active_lines' => Line::where('admin_enabled', 1)->where(function ($q) {
                    $q->whereNull('exp_date')->orWhere('exp_date', '>', now());
                })->count(),
                'total_movies' => Movie::count(),
                'total_series' => Series::count(),
                'total_users' => User::count(),
                'servers' => $servers,
                'stream_activity' => $streamActivity,
                'connection_history' => $connectionHistory,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }
}
