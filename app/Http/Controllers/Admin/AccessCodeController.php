<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Security\Models\AccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AccessCodeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/AccessCodes/Index', [
            'codes' => AccessCode::orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/AccessCodes/Create'); }

    public function store(Request $request)
    {
        $data = $request->validate(['code' => 'nullable|string|max:255', 'type' => 'required|integer|min:0|max:6']);
        if (empty($data['code'])) $data['code'] = Str::random(12);
        AccessCode::create($data);
        return redirect()->route('admin.access-codes.index')->with('success', 'Access code created.');
    }

    public function edit(AccessCode $access_code) { return Inertia::render('Admin/AccessCodes/Edit', ['code' => $access_code]); }

    public function update(Request $request, AccessCode $access_code)
    {
        $access_code->update($request->validate(['code' => 'required|string|max:255', 'type' => 'required|integer|min:0|max:6']));
        return redirect()->route('admin.access-codes.index')->with('success', 'Access code updated.');
    }

    public function destroy(AccessCode $access_code)
    {
        $access_code->delete();
        return redirect()->route('admin.access-codes.index')->with('success', 'Access code deleted.');
    }
}
