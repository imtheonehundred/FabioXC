<?php

namespace App\Providers;

use App\Modules\Core\EventDispatcher;
use App\Modules\Core\ModuleLoader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventDispatcher::class);
        $this->app->singleton(ModuleLoader::class);
    }

    public function boot(): void
    {
        $loader = $this->app->make(ModuleLoader::class);
        $loader->loadAll();

        $dispatcher = $this->app->make(EventDispatcher::class);
        foreach ($loader->getAllEventSubscribers() as $event => $handler) {
            if (is_array($handler) && is_string($handler[0])) {
                $dispatcher->subscribe($event, function ($payload) use ($handler) {
                    $instance = app($handler[0]);
                    return $instance->{$handler[1]}($payload);
                });
            } elseif (is_callable($handler)) {
                $dispatcher->subscribe($event, $handler);
            }
        }
    }
}
