<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');
        if ($search = $request->input('search')) {
            $query->where('action', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
        }
        if ($request->filled('action')) $query->where('action', $request->input('action'));
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'desc'));
        else $query->orderByDesc('created_at');

        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $query->paginate($request->input('per_page', 50))->withQueryString(),
            'actions' => $actions,
            'filters' => $request->only(['search', 'sort', 'direction', 'action']),
        ]);
    }
}
