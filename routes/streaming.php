<?php

use App\Http\Controllers\StreamingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Streaming Routes
|--------------------------------------------------------------------------
| These routes bypass web middleware (no sessions, CSRF, Inertia).
| Handles the hot path: auth -> protection -> delivery.
|
| URL patterns match standard XC API format:
|   /live/{username}/{password}/{stream_id}.ts
|   /movie/{username}/{password}/{vod_id}.mp4
|   /series/{username}/{password}/{episode_id}.mp4
|   /timeshift/{username}/{password}/{stream_id}?start=TIMESTAMP&duration=SECONDS
|   /streaming/segment/{stream_id}/{segment}.ts
|   /streaming/hls/{stream_id}/index.m3u8
*/

// Live streaming
Route::get('live/{username}/{password}/{stream_id}.ts', [StreamingController::class, 'live']);
Route::get('live/{username}/{password}/{stream_id}.m3u8', [StreamingController::class, 'live']);
Route::get('live/{username}/{password}/{stream_id}', [StreamingController::class, 'live']);

// VOD (movies)
Route::get('movie/{username}/{password}/{vod_id}.{ext}', [StreamingController::class, 'vod']);
Route::get('movie/{username}/{password}/{vod_id}', [StreamingController::class, 'vod']);

// Series (episodes)
Route::get('series/{username}/{password}/{episode_id}.{ext}', [StreamingController::class, 'series']);
Route::get('series/{username}/{password}/{episode_id}', [StreamingController::class, 'series']);

// Timeshift/catchup
Route::get('timeshift/{username}/{password}/{stream_id}', [StreamingController::class, 'timeshift']);
Route::get('timeshift/{username}/{password}/{stream_id}.ts', [StreamingController::class, 'timeshift']);

// HLS segment delivery
Route::get('streaming/segment/{stream_id}/{segment}', [StreamingController::class, 'segment']);
Route::get('streaming/hls/{stream_id}/index.m3u8', [StreamingController::class, 'hlsPlaylist']);
Route::get('streaming/hls/{stream_id}/{segment}.ts', [StreamingController::class, 'segment']);

// Timeshift segment delivery
Route::get('streaming/timeshift/{stream_id}/{segment}.ts', function (int $stream_id, string $segment) {
    $delivery = app(\App\Streaming\Delivery\TimeshiftDelivery::class);
    return $delivery->serveSegment($stream_id, (int) $segment);
});
