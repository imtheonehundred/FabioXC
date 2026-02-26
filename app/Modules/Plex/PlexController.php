<?php

namespace App\Modules\Plex;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlexController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Modules/Plex', [
            'servers' => [],
            'libraries' => [],
        ]);
    }

    public function sync(Request $request)
    {
        return back()->with('success', 'Plex sync started.');
    }

    public function settings()
    {
        return Inertia::render('Admin/Modules/PlexSettings', [
            'settings' => config('plex', []),
        ]);
    }

    public function updateSettings(Request $request)
    {
        return back()->with('success', 'Plex settings updated.');
    }
}
