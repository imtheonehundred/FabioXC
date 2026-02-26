<?php

namespace App\Modules\Core;

use Illuminate\Support\Facades\Route;

interface ModuleInterface
{
    public function getName(): string;

    public function getVersion(): string;

    public function getDescription(): string;

    public function boot(): void;

    public function registerRoutes(): void;

    public function registerCrons(): array;

    public function getEventSubscribers(): array;
}
