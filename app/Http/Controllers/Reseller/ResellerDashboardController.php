<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Stream\Models\Stream;
use Inertia\Inertia;

class ResellerDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        return Inertia::render('Reseller/Dashboard', [
            'stats' => [
                'total_lines' => Line::where('created_by', $userId)->count(),
                'active_lines' => Line::where('created_by', $userId)->where('admin_enabled', 1)
                    ->where(fn ($q) => $q->whereNull('exp_date')->orWhere('exp_date', '>', now()))->count(),
                'total_connections' => Line::where('created_by', $userId)->sum('active_connections'),
                'active_streams' => Stream::where('status', 1)->count(),
                'total_streams' => Stream::count(),
            ],
        ]);
    }
}
