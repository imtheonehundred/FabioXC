<?php

namespace App\Modules\Fingerprint;

use App\Modules\Core\ModuleInterface;

class FingerprintModule implements ModuleInterface
{
    public function getName(): string { return 'fingerprint'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Watermark fingerprinting for stream piracy tracking'; }
    public function boot(): void {}
    public function registerRoutes(): void {}
    public function registerCrons(): array { return []; }
    public function getEventSubscribers(): array
    {
        return ['stream.started' => [self::class, 'onStreamStarted']];
    }

    public static function onStreamStarted(mixed $payload): void {}
}
