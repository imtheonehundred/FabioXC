<?php

namespace App\Modules\TheftDetection;

use App\Modules\Core\ModuleInterface;

class TheftDetectionModule implements ModuleInterface
{
    public function getName(): string { return 'theft-detection'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Anti-piracy restream detection and alerting'; }
    public function boot(): void {}
    public function registerRoutes(): void {}
    public function registerCrons(): array { return []; }
    public function getEventSubscribers(): array { return []; }
}
