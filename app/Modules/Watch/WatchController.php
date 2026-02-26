<?php

namespace App\Modules\Watch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WatchController extends Controller
{
    public function index() { return Inertia::render('Admin/Modules/Watch', ['folders' => [], 'categories' => []]); }
    public function scan() { return back()->with('success', 'Watch folder scan started.'); }
    public function recordings() { return Inertia::render('Admin/Modules/WatchRecordings', ['recordings' => []]); }
    public function scheduleRecording(Request $request) { return back()->with('success', 'Recording scheduled.'); }
    public function deleteRecording(int $id) { return back()->with('success', 'Recording deleted.'); }
}
