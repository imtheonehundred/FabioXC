<?php

namespace App\Modules\Tmdb;

use App\Modules\Core\ModuleInterface;

class TmdbModule implements ModuleInterface
{
    public function getName(): string { return 'tmdb'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'TMDB metadata fetching for movies and series'; }

    public function boot(): void {}
    public function registerRoutes(): void {}

    public function registerCrons(): array
    {
        return [
            ['schedule' => 'daily', 'command' => 'tmdb:update', 'description' => 'Update TMDB metadata'],
            ['schedule' => 'weekly', 'command' => 'tmdb:popular', 'description' => 'Fetch popular titles from TMDB'],
        ];
    }

    public function getEventSubscribers(): array
    {
        return [
            'movie.created' => [TmdbService::class, 'onMovieCreated'],
            'series.created' => [TmdbService::class, 'onSeriesCreated'],
        ];
    }
}
