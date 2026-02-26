<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\BlockedIsp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockedIspController extends Controller
{
    public function index(Request $request)
    {
        $query = BlockedIsp::query();
        if ($search = $request->input('search')) $query->where('isp_name', 'like', "%{$search}%");
        return Inertia::render('Admin/BlockedIsps/Index', [
            'blockedIsps' => $query->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/BlockedIsps/Create'); }

    public function store(Request $request)
    {
        BlockedIsp::create($request->validate(['isp_name' => 'required|string|max:255', 'reason' => 'nullable|string']));
        return redirect()->route('admin.blocked-isps.index')->with('success', 'ISP blocked.');
    }

    public function destroy(BlockedIsp $blocked_isp)
    {
        $blocked_isp->delete();
        return redirect()->route('admin.blocked-isps.index')->with('success', 'ISP unblocked.');
    }
}
