<?php

use Illuminate\Support\Facades\Schedule;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\StreamProcess;
use App\Domain\Server\Models\Server;
use App\Domain\Line\Models\Line;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Modules\Core\ModuleLoader;

// Module crons (ARCHITECTURE §6, §3.7) — from config/modules.php
$moduleLoader = app(ModuleLoader::class);
foreach ($moduleLoader->getAllCrons() as $cron) {
    $schedule = $cron['schedule'] ?? 'hourly';
    $command = $cron['command'] ?? '';
    if ($command === '') continue;
    $event = Schedule::command($command)->withoutOverlapping();
    match ($schedule) {
        'everyMinute' => $event->everyMinute(),
        'everyFiveMinutes' => $event->everyFiveMinutes(),
        'everyTenMinutes' => $event->everyTenMinutes(),
        'everyFifteenMinutes' => $event->everyFifteenMinutes(),
        'everyThirtyMinutes' => $event->everyThirtyMinutes(),
        'hourly' => $event->hourly(),
        'daily' => $event->daily(),
        'weekly' => $event->weekly(),
        'monthly' => $event->monthly(),
        default => $event->hourly(),
    };
}

// Streams Cron — check stream health, restart failed ones
Schedule::call(function () {
    $streams = Stream::where('admin_enabled', 1)->where('status', 1)->get();
    $autoRestart = DB::table('settings')->where('key', 'auto_restart_streams')->value('value');
    $streamProcess = app(StreamProcess::class);

    foreach ($streams as $stream) {
        if ($stream->pid && function_exists('posix_kill') && !posix_kill($stream->pid, 0)) {
            Log::warning("StreamsCron: Stream #{$stream->id} PID {$stream->pid} dead");
            $stream->update(['status' => 3, 'pid' => null]);
            if ($autoRestart && $stream->type !== 'radio') {
                $streamProcess->start($stream);
            }
        }
    }
})->everyMinute()->name('streams-cron')->withoutOverlapping();

// Servers Cron — update server status
Schedule::call(function () {
    $servers = Server::where('status', 1)->get();
    foreach ($servers as $server) {
        if ($server->last_check_at && $server->last_check_at->diffInMinutes(now()) > 5) {
            $server->update(['status' => 0]);
            Log::warning("ServersCron: Server #{$server->id} marked offline — no heartbeat");
        }
    }
})->everyMinute()->name('servers-cron')->withoutOverlapping();

// Cache Cron — refresh cached data
Schedule::call(function () {
    Cache::forget('streaming:blocked_ips');
    Cache::forget('streaming:blocked_uas');
    Cache::forget('streaming:blocked_isps');
    Log::info('CacheCron: Streaming caches refreshed');
})->everyFiveMinutes()->name('cache-cron');

// EPG Cron — placeholder for EPG XML import
Schedule::call(function () {
    $interval = DB::table('settings')->where('key', 'epg_update_interval')->value('value') ?? 24;
    $epgs = DB::table('epg')->whereNull('last_updated_at')
        ->orWhere('last_updated_at', '<', now()->subHours((int) $interval))->get();
    foreach ($epgs as $epg) {
        DB::table('epg')->where('id', $epg->id)->update(['last_updated_at' => now()]);
        Log::info("EpgCron: Updated EPG #{$epg->id} ({$epg->epg_name})");
    }
})->hourly()->name('epg-cron')->withoutOverlapping();

// Cleanup Cron — remove old logs and temp files
Schedule::call(function () {
    $cutoff = now()->subDays(30);
    $deleted = DB::table('activity_logs')->where('created_at', '<', $cutoff)->delete();
    if ($deleted > 0) Log::info("CleanupCron: Deleted {$deleted} old activity logs");
})->daily()->name('cleanup-cron');

// Backups Cron — placeholder for database backup
Schedule::call(function () {
    Log::info('BackupsCron: Backup task triggered');
})->dailyAt('03:00')->name('backups-cron');

// Stats Cron — aggregate connection statistics
Schedule::call(function () {
    $totalConns = Line::sum('active_connections');
    $activeStreams = Stream::where('status', 1)->count();
    Log::info("StatsCron: {$totalConns} connections, {$activeStreams} active streams");
})->everyFiveMinutes()->name('stats-cron');

// Logs Cron — rotate and compress old log files
Schedule::call(function () {
    $logPath = storage_path('logs');
    $files = glob("{$logPath}/laravel-*.log");
    $cutoff = now()->subDays(7)->timestamp;
    foreach ($files as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
        }
    }
})->daily()->name('logs-cron');

// VOD Cron — check VOD file availability
Schedule::call(function () {
    $movies = Movie::where('admin_enabled', 1)->where('status', 1)->get();
    foreach ($movies as $movie) {
        $source = $movie->stream_source;
        if (!str_starts_with($source, 'http') && !file_exists($source)) {
            $movie->update(['status' => 0]);
            Log::warning("VodCron: Movie #{$movie->id} file missing: {$source}");
        }
    }
})->hourly()->name('vod-cron')->withoutOverlapping();
