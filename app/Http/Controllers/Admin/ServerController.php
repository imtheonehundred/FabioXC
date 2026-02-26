<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Server\ServerService;
use App\Domain\Server\Models\Server;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function __construct(
        private ServerService $serverService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Servers/Index', [
            'servers' => $this->serverService->list(
                $request->only(['search', 'sort', 'direction']),
                (int) $request->input('per_page', 25)
            ),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Servers/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'server_name' => 'required|string|max:255',
            'server_ip' => 'required|string|max:255',
            'domain_name' => 'nullable|string|max:255',
            'http_port' => 'required|integer|min:1|max:65535',
            'rtmp_port' => 'required|integer|min:1|max:65535',
        ]);
        $this->serverService->create($data);
        return redirect()->route('admin.servers.index')->with('success', 'Server created.');
    }

    public function show(Server $server)
    {
        $server->loadCount('streams');
        return Inertia::render('Admin/Servers/Show', ['server' => $server]);
    }

    public function edit(Server $server)
    {
        return Inertia::render('Admin/Servers/Edit', ['server' => $server]);
    }

    public function update(Request $request, Server $server)
    {
        $data = $request->validate([
            'server_name' => 'required|string|max:255',
            'server_ip' => 'required|string|max:255',
            'domain_name' => 'nullable|string|max:255',
            'http_port' => 'required|integer|min:1|max:65535',
            'rtmp_port' => 'required|integer|min:1|max:65535',
        ]);
        $this->serverService->update($server, $data);
        return redirect()->route('admin.servers.index')->with('success', 'Server updated.');
    }

    public function destroy(Server $server)
    {
        $this->serverService->delete($server);
        return redirect()->route('admin.servers.index')->with('success', 'Server deleted.');
    }
}
