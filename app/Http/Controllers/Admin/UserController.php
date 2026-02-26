<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domain\User\Models\MemberGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('group');
        if ($search = $request->input('search')) {
            $query->where('username', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'groups' => MemberGroup::orderBy('group_name')->get(['id', 'group_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:6|max:255',
            'email' => 'nullable|email|max:255',
            'member_group_id' => 'nullable|exists:member_groups,id',
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'groups' => MemberGroup::orderBy('group_name')->get(['id', 'group_name']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => "required|string|unique:users,username,{$user->id}|max:255",
            'password' => 'nullable|string|min:6|max:255',
            'email' => 'nullable|email|max:255',
            'member_group_id' => 'nullable|exists:member_groups,id',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
