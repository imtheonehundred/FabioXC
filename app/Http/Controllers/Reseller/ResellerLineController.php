<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResellerLineController extends Controller
{
    public function index(Request $request)
    {
        $query = Line::where('created_by', auth()->id())->with('packages');
        if ($search = $request->input('search')) $query->where('username', 'like', "%{$search}%");
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'asc'));
        else $query->orderByDesc('id');

        return Inertia::render('Reseller/Lines/Index', [
            'lines' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Reseller/Lines/Create', [
            'packages' => Package::where('is_official', 1)->orderBy('package_name')->get(['id', 'package_name']),
            'bouquets' => Bouquet::orderBy('bouquet_name')->get(['id', 'bouquet_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:lines,username|max:255',
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date', 'max_connections' => 'required|integer|min:1|max:5',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array',
        ]);
        $packageIds = $data['package_ids'] ?? []; unset($data['package_ids']);
        $data['added'] = now(); $data['created_by'] = auth()->id();
        $line = Line::create($data);
        if ($packageIds) $line->packages()->attach($packageIds);
        return redirect()->route('reseller.lines.index')->with('success', 'Line created.');
    }

    public function edit(Line $line)
    {
        if ($line->created_by !== auth()->id()) abort(403);
        return Inertia::render('Reseller/Lines/Edit', [
            'line' => $line->load('packages'),
            'packages' => Package::where('is_official', 1)->orderBy('package_name')->get(['id', 'package_name']),
            'bouquets' => Bouquet::orderBy('bouquet_name')->get(['id', 'bouquet_name']),
        ]);
    }

    public function update(Request $request, Line $line)
    {
        if ($line->created_by !== auth()->id()) abort(403);
        $data = $request->validate([
            'username' => "required|string|unique:lines,username,{$line->id}|max:255",
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date', 'max_connections' => 'required|integer|min:1|max:5',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array',
        ]);
        if (isset($data['package_ids'])) { $line->packages()->sync($data['package_ids']); unset($data['package_ids']); }
        $line->update($data);
        return redirect()->route('reseller.lines.index')->with('success', 'Line updated.');
    }

    public function destroy(Line $line)
    {
        if ($line->created_by !== auth()->id()) abort(403);
        $line->delete();
        return redirect()->route('reseller.lines.index')->with('success', 'Line deleted.');
    }
}
