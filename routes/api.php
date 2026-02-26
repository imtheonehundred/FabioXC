<?php

use App\Http\Controllers\Api\PlayerApiController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\ResellerApiController;
use App\Http\Controllers\Api\InternalApiController;
use Illuminate\Support\Facades\Route;

// Player API — public, line credentials inline
Route::prefix('player')->group(function () {
    Route::get('login', [PlayerApiController::class, 'login']);
    Route::get('get_live_categories', [PlayerApiController::class, 'getLiveCategories']);
    Route::get('get_live_streams', [PlayerApiController::class, 'getLiveStreams']);
    Route::get('get_vod_categories', [PlayerApiController::class, 'getVodCategories']);
    Route::get('get_vod_streams', [PlayerApiController::class, 'getVodStreams']);
    Route::get('get_series_categories', [PlayerApiController::class, 'getSeriesCategories']);
    Route::get('get_series', [PlayerApiController::class, 'getSeries']);
    Route::get('get_series_info', [PlayerApiController::class, 'getSeriesInfo']);
    Route::get('get_vod_info', [PlayerApiController::class, 'getVodInfo']);
    Route::get('get_epg', [PlayerApiController::class, 'getEpg']);
    Route::get('get_on_demand_quick', [PlayerApiController::class, 'getOnDemandQuick']);
    Route::get('xmltv.php', [PlayerApiController::class, 'xmltv']);
});

// Admin API — token auth, full access
Route::prefix('admin')->middleware('api.token:admin')->group(function () {
    // Streams
    Route::get('streams', [AdminApiController::class, 'listStreams']);
    Route::get('streams/{id}', [AdminApiController::class, 'getStream']);
    Route::post('streams', [AdminApiController::class, 'createStream']);
    Route::put('streams/{id}', [AdminApiController::class, 'updateStream']);
    Route::delete('streams/{id}', [AdminApiController::class, 'deleteStream']);
    Route::post('streams/mass-action', [AdminApiController::class, 'massActionStreams']);

    // Categories
    Route::get('categories', [AdminApiController::class, 'listCategories']);
    Route::get('categories/{id}', [AdminApiController::class, 'getCategory']);
    Route::post('categories', [AdminApiController::class, 'createCategory']);
    Route::put('categories/{id}', [AdminApiController::class, 'updateCategory']);
    Route::delete('categories/{id}', [AdminApiController::class, 'deleteCategory']);

    // Movies
    Route::get('movies', [AdminApiController::class, 'listMovies']);
    Route::get('movies/{id}', [AdminApiController::class, 'getMovie']);
    Route::post('movies', [AdminApiController::class, 'createMovie']);
    Route::put('movies/{id}', [AdminApiController::class, 'updateMovie']);
    Route::delete('movies/{id}', [AdminApiController::class, 'deleteMovie']);

    // Series
    Route::get('series', [AdminApiController::class, 'listSeries']);
    Route::get('series/{id}', [AdminApiController::class, 'getSeries']);
    Route::post('series', [AdminApiController::class, 'createSeries']);
    Route::put('series/{id}', [AdminApiController::class, 'updateSeries']);
    Route::delete('series/{id}', [AdminApiController::class, 'deleteSeries']);

    // Episodes
    Route::get('series/{seriesId}/episodes', [AdminApiController::class, 'listEpisodes']);
    Route::post('series/{seriesId}/episodes', [AdminApiController::class, 'createEpisode']);
    Route::put('episodes/{id}', [AdminApiController::class, 'updateEpisode']);
    Route::delete('episodes/{id}', [AdminApiController::class, 'deleteEpisode']);

    // Lines
    Route::get('lines', [AdminApiController::class, 'listLines']);
    Route::get('lines/{id}', [AdminApiController::class, 'getLine']);
    Route::post('lines', [AdminApiController::class, 'createLine']);
    Route::put('lines/{id}', [AdminApiController::class, 'updateLine']);
    Route::delete('lines/{id}', [AdminApiController::class, 'deleteLine']);
    Route::post('lines/mass-action', [AdminApiController::class, 'massActionLines']);

    // Users
    Route::get('users', [AdminApiController::class, 'listUsers']);
    Route::get('users/{id}', [AdminApiController::class, 'getUser']);
    Route::post('users', [AdminApiController::class, 'createUser']);
    Route::put('users/{id}', [AdminApiController::class, 'updateUser']);
    Route::delete('users/{id}', [AdminApiController::class, 'deleteUser']);

    // Servers
    Route::get('servers', [AdminApiController::class, 'listServers']);
    Route::get('servers/{id}', [AdminApiController::class, 'getServer']);
    Route::post('servers', [AdminApiController::class, 'createServer']);
    Route::put('servers/{id}', [AdminApiController::class, 'updateServer']);
    Route::delete('servers/{id}', [AdminApiController::class, 'deleteServer']);

    // Bouquets
    Route::get('bouquets', [AdminApiController::class, 'listBouquets']);
    Route::get('bouquets/{id}', [AdminApiController::class, 'getBouquet']);
    Route::post('bouquets', [AdminApiController::class, 'createBouquet']);
    Route::put('bouquets/{id}', [AdminApiController::class, 'updateBouquet']);
    Route::delete('bouquets/{id}', [AdminApiController::class, 'deleteBouquet']);

    // Packages
    Route::get('packages', [AdminApiController::class, 'listPackages']);
    Route::get('packages/{id}', [AdminApiController::class, 'getPackage']);
    Route::post('packages', [AdminApiController::class, 'createPackage']);
    Route::put('packages/{id}', [AdminApiController::class, 'updatePackage']);
    Route::delete('packages/{id}', [AdminApiController::class, 'deletePackage']);

    // EPG
    Route::get('epg', [AdminApiController::class, 'listEpg']);
    Route::get('epg/{id}', [AdminApiController::class, 'getEpg']);
    Route::post('epg', [AdminApiController::class, 'createEpg']);
    Route::put('epg/{id}', [AdminApiController::class, 'updateEpg']);
    Route::delete('epg/{id}', [AdminApiController::class, 'deleteEpg']);

    // Settings
    Route::get('settings', [AdminApiController::class, 'getSettings']);
    Route::put('settings', [AdminApiController::class, 'updateSettings']);
});

// Reseller API — token auth, scoped to own resources
Route::prefix('reseller')->middleware('api.token:reseller')->group(function () {
    Route::get('lines', [ResellerApiController::class, 'listLines']);
    Route::get('lines/{id}', [ResellerApiController::class, 'getLine']);
    Route::post('lines', [ResellerApiController::class, 'createLine']);
    Route::put('lines/{id}', [ResellerApiController::class, 'updateLine']);
    Route::delete('lines/{id}', [ResellerApiController::class, 'deleteLine']);

    Route::get('users', [ResellerApiController::class, 'listUsers']);
    Route::post('users', [ResellerApiController::class, 'createUser']);

    Route::get('packages', [ResellerApiController::class, 'listPackages']);
    Route::get('server', [ResellerApiController::class, 'getServerInfo']);
    Route::get('profile', [ResellerApiController::class, 'getProfile']);
});

// Internal API — server key auth, server-to-server
Route::prefix('internal')->middleware('api.server')->group(function () {
    Route::post('heartbeat', [InternalApiController::class, 'heartbeat']);
    Route::get('stream/{id}', [InternalApiController::class, 'getStreamConfig']);
    Route::get('line/validate', [InternalApiController::class, 'validateLine']);
    Route::post('connections/update', [InternalApiController::class, 'updateConnections']);
    Route::get('server/config', [InternalApiController::class, 'getServerConfig']);
});
