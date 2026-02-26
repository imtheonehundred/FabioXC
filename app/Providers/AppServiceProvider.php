<?php

namespace App\Providers;

use App\Modules\Core\EventDispatcher;
use App\Modules\Core\ModuleLoader;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->registerStreamingRateLimiter();

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

    private function registerStreamingRateLimiter(): void
    {
        RateLimiter::for('streaming', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
