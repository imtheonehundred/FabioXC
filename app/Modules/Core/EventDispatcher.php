<?php

namespace App\Modules\Core;

use Illuminate\Support\Facades\Log;

class EventDispatcher
{
    private array $listeners = [];

    public function subscribe(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, mixed $payload = null): array
    {
        $results = [];

        foreach ($this->listeners[$event] ?? [] as $listener) {
            try {
                $results[] = $listener($payload);
            } catch (\Throwable $e) {
                Log::error("Event listener failed for '{$event}': {$e->getMessage()}");
            }
        }

        return $results;
    }

    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]);
    }

    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }
}
