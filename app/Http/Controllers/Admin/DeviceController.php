<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Device\Models\Device;
use App\Domain\Line\Models\Line;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with('line');
        if ($search = $request->input('search')) {
            $query->where('mac_address', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%");
        }
        if ($request->filled('device_type')) $query->where('device_type', $request->input('device_type'));
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'asc'));
        else $query->orderByDesc('id');

        return Inertia::render('Admin/Devices/Index', [
            'devices' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'device_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Devices/Create', [
            'lines' => Line::orderBy('username')->get(['id', 'username']),
        ]);
    }

    public function store(Request $request)
    {
        Device::create($request->validate([
            'mac_address' => 'required|string|unique:devices,mac_address|max:255',
            'device_type' => 'required|in:mag,enigma,stb',
            'line_id' => 'nullable|exists:lines,id', 'model' => 'nullable|string|max:255',
            'admin_enabled' => 'boolean', 'notes' => 'nullable|string',
        ]));
        return redirect()->route('admin.devices.index')->with('success', 'Device created.');
    }

    public function edit(Device $device)
    {
        return Inertia::render('Admin/Devices/Edit', [
            'device' => $device, 'lines' => Line::orderBy('username')->get(['id', 'username']),
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $device->update($request->validate([
            'mac_address' => "required|string|unique:devices,mac_address,{$device->id}|max:255",
            'device_type' => 'required|in:mag,enigma,stb',
            'line_id' => 'nullable|exists:lines,id', 'model' => 'nullable|string|max:255',
            'admin_enabled' => 'boolean', 'notes' => 'nullable|string',
        ]));
        return redirect()->route('admin.devices.index')->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('admin.devices.index')->with('success', 'Device deleted.');
    }
}
