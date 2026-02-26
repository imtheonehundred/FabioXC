<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LineController extends Controller
{
    public function index(Request $request)
    {
        $query = Line::with('packages');
        if ($search = $request->input('search')) {
            $query->where('username', 'like', "%{$search}%");
        }
        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('admin_enabled', 1)->where(fn($q) => $q->whereNull('exp_date')->orWhere('exp_date', '>', now())),
                'expired' => $query->where('exp_date', '<', now()),
                'disabled' => $query->where('admin_enabled', 0),
                default => null,
            };
        }
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        return Inertia::render('Admin/Lines/Index', [
            'lines' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Lines/Create', [
            'packages' => Package::orderBy('package_name')->get(['id', 'package_name', 'is_trial']),
            'bouquets' => Bouquet::orderBy('bouquet_name')->get(['id', 'bouquet_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:lines,username|max:255',
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date',
            'max_connections' => 'required|integer|min:1',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean', 'is_restreamer' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array', 'package_ids.*' => 'exists:packages,id',
        ]);

        $packageIds = $data['package_ids'] ?? [];
        unset($data['package_ids']);
        $data['added'] = now();
        $data['created_by'] = auth()->id();

        $line = Line::create($data);
        if (!empty($packageIds)) {
            $line->packages()->attach($packageIds);
        }

        return redirect()->route('admin.lines.index')->with('success', 'Line created.');
    }

    public function edit(Line $line)
    {
        $line->load('packages');
        return Inertia::render('Admin/Lines/Edit', [
            'line' => $line,
            'packages' => Package::orderBy('package_name')->get(['id', 'package_name', 'is_trial']),
            'bouquets' => Bouquet::orderBy('bouquet_name')->get(['id', 'bouquet_name']),
        ]);
    }

    public function update(Request $request, Line $line)
    {
        $data = $request->validate([
            'username' => "required|string|unique:lines,username,{$line->id}|max:255",
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date',
            'max_connections' => 'required|integer|min:1',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean', 'is_restreamer' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array', 'package_ids.*' => 'exists:packages,id',
        ]);

        $packageIds = $data['package_ids'] ?? [];
        unset($data['package_ids']);

        $line->update($data);
        $line->packages()->sync($packageIds);

        return redirect()->route('admin.lines.index')->with('success', 'Line updated.');
    }

    public function destroy(Line $line)
    {
        $line->delete();
        return redirect()->route('admin.lines.index')->with('success', 'Line deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete,enable,disable', 'ids' => 'required|array', 'ids.*' => 'exists:lines,id']);
        match ($request->input('action')) {
            'delete' => Line::whereIn('id', $request->input('ids'))->delete(),
            'enable' => Line::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 1]),
            'disable' => Line::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 0]),
        };
        return back()->with('success', ucfirst($request->input('action')) . " applied.");
    }
}
