<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\LineController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BouquetController;
use App\Http\Controllers\Admin\EpgController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\BlockedIpController;
use App\Http\Controllers\Admin\BlockedIspController;
use App\Http\Controllers\Admin\BlockedUaController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AccessCodeController;
use App\Http\Controllers\Admin\LiveConnectionController;
use App\Http\Controllers\Admin\TranscodeProfileController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('streams', StreamController::class);
    Route::post('streams/mass-action', [StreamController::class, 'massAction'])->name('streams.mass-action');

    Route::resource('categories', CategoryController::class);

    Route::resource('movies', MovieController::class);
    Route::post('movies/mass-action', [MovieController::class, 'massAction'])->name('movies.mass-action');

    Route::resource('series', SeriesController::class);
    Route::post('series/mass-action', [SeriesController::class, 'massAction'])->name('series.mass-action');

    Route::resource('series.episodes', EpisodeController::class)->shallow();
    Route::post('episodes/mass-action', [EpisodeController::class, 'massAction'])->name('episodes.mass-action');

    Route::resource('lines', LineController::class);
    Route::post('lines/mass-action', [LineController::class, 'massAction'])->name('lines.mass-action');

    Route::resource('packages', PackageController::class);
    Route::resource('bouquets', BouquetController::class);
    Route::resource('users', UserController::class);
    Route::resource('groups', GroupController::class);
    Route::resource('servers', ServerController::class);
    Route::resource('devices', DeviceController::class);

    Route::resource('epgs', EpgController::class);
    Route::resource('access-codes', AccessCodeController::class);
    Route::resource('transcode-profiles', TranscodeProfileController::class);

    Route::resource('blocked-ips', BlockedIpController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('blocked-isps', BlockedIspController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('blocked-uas', BlockedUaController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('connections', [LiveConnectionController::class, 'index'])->name('connections.index');

    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Reseller Panel
Route::middleware(['auth', 'reseller'])->prefix('reseller')->name('reseller.')->group(function () {
    Route::get('/', [App\Http\Controllers\Reseller\ResellerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('lines', App\Http\Controllers\Reseller\ResellerLineController::class);
    Route::get('streams', [App\Http\Controllers\Reseller\ResellerStreamController::class, 'index'])->name('streams.index');
    Route::get('users', [App\Http\Controllers\Reseller\ResellerUserController::class, 'index'])->name('users.index');
    Route::get('tickets', [App\Http\Controllers\Reseller\ResellerTicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [App\Http\Controllers\Reseller\ResellerTicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [App\Http\Controllers\Reseller\ResellerTicketController::class, 'store'])->name('tickets.store');
    Route::get('profile', [App\Http\Controllers\Reseller\ResellerProfileController::class, 'index'])->name('profile');
    Route::put('profile', [App\Http\Controllers\Reseller\ResellerProfileController::class, 'update'])->name('profile.update');
});

Route::get('/player', fn () => view('player'))->name('player');

Route::redirect('/', '/admin');
