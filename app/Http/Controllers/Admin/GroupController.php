<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\User\Models\MemberGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Groups/Index', [
            'groups' => MemberGroup::withCount('users')->orderBy('group_name')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/Groups/Create'); }

    public function store(Request $request)
    {
        MemberGroup::create($request->validate(['group_name' => 'required|string|max:255', 'permissions' => 'nullable|array']));
        return redirect()->route('admin.groups.index')->with('success', 'Group created.');
    }

    public function edit(MemberGroup $group) { return Inertia::render('Admin/Groups/Edit', ['group' => $group]); }

    public function update(Request $request, MemberGroup $group)
    {
        $group->update($request->validate(['group_name' => 'required|string|max:255', 'permissions' => 'nullable|array']));
        return redirect()->route('admin.groups.index')->with('success', 'Group updated.');
    }

    public function destroy(MemberGroup $group)
    {
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'Group deleted.');
    }
}
