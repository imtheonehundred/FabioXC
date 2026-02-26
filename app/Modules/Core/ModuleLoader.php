<?php

namespace App\Modules\Core;

use Illuminate\Support\Facades\Log;

class ModuleLoader
{
    private array $modules = [];
    private array $loaded = [];

    public function loadAll(): void
    {
        $configPath = config_path('modules.php');
        if (!file_exists($configPath)) return;

        $moduleConfig = require $configPath;

        foreach ($moduleConfig as $name => $config) {
            if (!($config['enabled'] ?? false)) continue;

            try {
                $this->load($name, $config);
            } catch (\Throwable $e) {
                Log::error("Failed to load module '{$name}': {$e->getMessage()}");
            }
        }
    }

    public function load(string $name, array $config): void
    {
        if (isset($this->loaded[$name])) return;

        $class = $config['class'] ?? null;
        if (!$class || !class_exists($class)) {
            throw new \RuntimeException("Module class '{$class}' not found for module '{$name}'");
        }

        if (isset($config['dependencies'])) {
            foreach ($config['dependencies'] as $dep) {
                if (!isset($this->loaded[$dep])) {
                    throw new \RuntimeException("Module '{$name}' requires '{$dep}' which is not loaded");
                }
            }
        }

        $module = app($class);
        if (!$module instanceof ModuleInterface) {
            throw new \RuntimeException("Module '{$name}' must implement ModuleInterface");
        }

        $module->boot();
        $module->registerRoutes();

        $this->modules[$name] = $module;
        $this->loaded[$name] = true;
    }

    public function isLoaded(string $name): bool
    {
        return isset($this->loaded[$name]);
    }

    public function getModule(string $name): ?ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    public function getAllModules(): array
    {
        return $this->modules;
    }

    public function getAllCrons(): array
    {
        $crons = [];
        foreach ($this->modules as $module) {
            $crons = array_merge($crons, $module->registerCrons());
        }
        return $crons;
    }

    public function getAllEventSubscribers(): array
    {
        $subscribers = [];
        foreach ($this->modules as $module) {
            $subscribers = array_merge($subscribers, $module->getEventSubscribers());
        }
        return $subscribers;
    }
}
