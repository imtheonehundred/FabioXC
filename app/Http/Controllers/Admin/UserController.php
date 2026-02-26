<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\User\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => $this->userService->list(
                $request->only(['search', 'sort', 'direction']),
                (int) $request->input('per_page', 25)
            ),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'groups' => $this->userService->getGroups(),
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
        $this->userService->create($data);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'groups' => $this->userService->getGroups(),
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
        $this->userService->update($user, $data);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $this->userService->delete($user);
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
