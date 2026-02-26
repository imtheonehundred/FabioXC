<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Line\LineService;
use App\Domain\Line\Models\Line;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LineController extends Controller
{
    public function __construct(
        private LineService $lineService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Lines/Index', [
            'lines' => $this->lineService->list(
                $request->only(['search', 'sort', 'direction', 'status']),
                (int) $request->input('per_page', 25)
            ),
            'filters' => $request->only(['search', 'sort', 'direction', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Lines/Create', [
            'packages' => $this->lineService->getPackages(),
            'bouquets' => $this->lineService->getBouquets(),
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

        $this->lineService->create($data, $data['package_ids'] ?? []);

        return redirect()->route('admin.lines.index')->with('success', 'Line created.');
    }

    public function edit(Line $line)
    {
        $line->load('packages');
        return Inertia::render('Admin/Lines/Edit', [
            'line' => $line,
            'packages' => $this->lineService->getPackages(),
            'bouquets' => $this->lineService->getBouquets(),
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

        $this->lineService->update($line, $data, $data['package_ids'] ?? []);

        return redirect()->route('admin.lines.index')->with('success', 'Line updated.');
    }

    public function destroy(Line $line)
    {
        $this->lineService->delete($line);
        return redirect()->route('admin.lines.index')->with('success', 'Line deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete,enable,disable', 'ids' => 'required|array', 'ids.*' => 'exists:lines,id']);
        $count = $this->lineService->massAction($request->input('action'), $request->input('ids'));
        return back()->with('success', ucfirst($request->input('action')) . " applied to {$count} lines.");
    }
}
