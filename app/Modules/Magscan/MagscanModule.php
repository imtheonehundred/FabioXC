<?php

namespace App\Modules\Magscan;

use App\Modules\Core\ModuleInterface;

class MagscanModule implements ModuleInterface
{
    public function getName(): string { return 'magscan'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'MAG device network scanning and discovery'; }
    public function boot(): void {}
    public function registerRoutes(): void {}
    public function registerCrons(): array
    {
        return [
            ['schedule' => 'hourly', 'command' => 'magscan:scan', 'description' => 'Scan network for MAG devices'],
        ];
    }
    public function getEventSubscribers(): array { return []; }
}
