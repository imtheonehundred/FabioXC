<?php

namespace App\Modules\Ministra;

use App\Modules\Core\ModuleInterface;
use Illuminate\Support\Facades\Route;

class MinistraModule implements ModuleInterface
{
    public function getName(): string { return 'ministra'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Ministra/Stalker Portal middleware for MAG devices'; }

    public function boot(): void {}

    public function registerRoutes(): void
    {
        Route::prefix('ministra')->group(function () {
            Route::get('/portal', [PortalController::class, 'handle']);
            Route::post('/portal', [PortalController::class, 'handle']);
        });
    }

    public function registerCrons(): array { return []; }
    public function getEventSubscribers(): array { return []; }
}
