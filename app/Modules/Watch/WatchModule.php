<?php

namespace App\Modules\Watch;

use App\Modules\Core\ModuleInterface;
use Illuminate\Support\Facades\Route;

class WatchModule implements ModuleInterface
{
    public function getName(): string { return 'watch'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Watch folder monitoring and DVR recording'; }

    public function boot(): void {}

    public function registerRoutes(): void
    {
        Route::middleware('auth')->prefix('admin/watch')->name('admin.watch.')->group(function () {
            Route::get('/', [WatchController::class, 'index'])->name('index');
            Route::post('/scan', [WatchController::class, 'scan'])->name('scan');
            Route::get('/recordings', [WatchController::class, 'recordings'])->name('recordings');
            Route::post('/record', [WatchController::class, 'scheduleRecording'])->name('record');
            Route::delete('/recordings/{id}', [WatchController::class, 'deleteRecording'])->name('recordings.destroy');
        ]);
    }

    public function registerCrons(): array
    {
        return [
            ['schedule' => 'everyFiveMinutes', 'command' => 'watch:scan', 'description' => 'Scan watch folders'],
        ];
    }

    public function getEventSubscribers(): array
    {
        return [
            'stream.started' => [RecordingService::class, 'onStreamStarted'],
        ];
    }
}
