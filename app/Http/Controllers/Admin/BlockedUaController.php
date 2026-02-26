<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\BlockedUa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockedUaController extends Controller
{
    public function index(Request $request)
    {
        $query = BlockedUa::query();
        if ($search = $request->input('search')) $query->where('user_agent', 'like', "%{$search}%");
        return Inertia::render('Admin/BlockedUas/Index', [
            'blockedUas' => $query->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/BlockedUas/Create'); }

    public function store(Request $request)
    {
        BlockedUa::create($request->validate(['user_agent' => 'required|string|max:255', 'reason' => 'nullable|string']));
        return redirect()->route('admin.blocked-uas.index')->with('success', 'User-Agent blocked.');
    }

    public function destroy(BlockedUa $blocked_ua)
    {
        $blocked_ua->delete();
        return redirect()->route('admin.blocked-uas.index')->with('success', 'User-Agent unblocked.');
    }
}
