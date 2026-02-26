<?php

namespace App\Modules\Plex;

use App\Modules\Core\ModuleInterface;
use Illuminate\Support\Facades\Route;

class PlexModule implements ModuleInterface
{
    public function getName(): string { return 'plex'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Plex Media Server integration for VOD import'; }

    public function boot(): void {}

    public function registerRoutes(): void
    {
        Route::middleware('auth')->prefix('admin/plex')->name('admin.plex.')->group(function () {
            Route::get('/', [PlexController::class, 'index'])->name('index');
            Route::post('/sync', [PlexController::class, 'sync'])->name('sync');
            Route::get('/settings', [PlexController::class, 'settings'])->name('settings');
            Route::post('/settings', [PlexController::class, 'updateSettings'])->name('settings.update');
        });
    }

    public function registerCrons(): array
    {
        return [
            ['schedule' => 'hourly', 'command' => 'plex:sync', 'description' => 'Sync Plex libraries'],
        ];
    }

    public function getEventSubscribers(): array { return []; }
}
