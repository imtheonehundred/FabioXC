<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ResellerProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('Reseller/Profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $user->update($data);
        return back()->with('success', 'Profile updated.');
    }
}
