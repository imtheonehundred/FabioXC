<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::withCount('lines');
        if ($search = $request->input('search')) $query->where('package_name', 'like', "%{$search}%");
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_name' => 'required|string|max:255',
            'is_trial' => 'boolean', 'is_official' => 'boolean', 'is_addon' => 'boolean',
        ]);
        Package::create($data);
        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function edit(Package $package)
    {
        return Inertia::render('Admin/Packages/Edit', ['package' => $package]);
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            'package_name' => 'required|string|max:255',
            'is_trial' => 'boolean', 'is_official' => 'boolean', 'is_addon' => 'boolean',
        ]);
        $package->update($data);
        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }
}
