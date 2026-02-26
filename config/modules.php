<?php

return [
    'ministra' => [
        'enabled' => true,
        'class' => \App\Modules\Ministra\MinistraModule::class,
        'dependencies' => [],
    ],
    'plex' => [
        'enabled' => true,
        'class' => \App\Modules\Plex\PlexModule::class,
        'dependencies' => [],
    ],
    'tmdb' => [
        'enabled' => true,
        'class' => \App\Modules\Tmdb\TmdbModule::class,
        'dependencies' => [],
    ],
    'watch' => [
        'enabled' => true,
        'class' => \App\Modules\Watch\WatchModule::class,
        'dependencies' => [],
    ],
    'fingerprint' => [
        'enabled' => true,
        'class' => \App\Modules\Fingerprint\FingerprintModule::class,
        'dependencies' => [],
    ],
    'theft-detection' => [
        'enabled' => true,
        'class' => \App\Modules\TheftDetection\TheftDetectionModule::class,
        'dependencies' => [],
    ],
    'magscan' => [
        'enabled' => true,
        'class' => \App\Modules\Magscan\MagscanModule::class,
        'dependencies' => [],
    ],
];
