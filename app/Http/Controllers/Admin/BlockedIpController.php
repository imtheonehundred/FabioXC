<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\BlockedIp;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockedIpController extends Controller
{
    public function index(Request $request)
    {
        $query = BlockedIp::query();
        if ($search = $request->input('search')) $query->where('ip_address', 'like', "%{$search}%");
        return Inertia::render('Admin/BlockedIps/Index', [
            'blockedIps' => $query->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/BlockedIps/Create'); }

    public function store(Request $request)
    {
        BlockedIp::create($request->validate(['ip_address' => 'required|string|max:255', 'reason' => 'nullable|string']));
        return redirect()->route('admin.blocked-ips.index')->with('success', 'IP blocked.');
    }

    public function destroy(BlockedIp $blocked_ip)
    {
        $blocked_ip->delete();
        return redirect()->route('admin.blocked-ips.index')->with('success', 'IP unblocked.');
    }
}
