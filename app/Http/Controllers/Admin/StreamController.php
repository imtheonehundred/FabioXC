<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Stream\StreamService;
use App\Domain\Stream\Models\Stream;
use App\Domain\Server\Models\Server;
use App\Domain\Epg\Models\Epg;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StreamController extends Controller
{
    public function __construct(
        private StreamService $streamService
    ) {}

    public function index(Request $request)
    {
        $streams = $this->streamService->list(
            $request->only(['search', 'sort', 'direction', 'category_id', 'status', 'type']),
            (int) $request->input('per_page', 25)
        );
        $categories = $this->streamService->getCategoriesForLive();

        return Inertia::render('Admin/Streams/Index', [
            'streams' => $streams,
            'categories' => $categories,
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id', 'status', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Streams/Create', [
            'categories' => $this->streamService->getCategoriesForLive(),
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
            'epgs' => Epg::orderBy('epg_name')->get(['id', 'epg_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'type' => 'required|in:live,created,radio',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'epg_id' => 'nullable|exists:epg,id',
            'stream_icon' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'admin_enabled' => 'boolean',
            'tv_archive' => 'boolean',
            'tv_archive_duration' => 'nullable|integer|min:0|max:720',
        ]);

        $this->streamService->create($data);

        return redirect()->route('admin.streams.index')->with('success', 'Stream created successfully.');
    }

    public function show(Stream $stream)
    {
        $stream->load(['category', 'server', 'epg']);

        return Inertia::render('Admin/Streams/Show', [
            'stream' => $stream,
        ]);
    }

    public function edit(Stream $stream)
    {
        return Inertia::render('Admin/Streams/Edit', [
            'stream' => $stream,
            'categories' => $this->streamService->getCategoriesForLive(),
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
            'epgs' => Epg::orderBy('epg_name')->get(['id', 'epg_name']),
        ]);
    }

    public function update(Request $request, Stream $stream)
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'type' => 'required|in:live,created,radio',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'epg_id' => 'nullable|exists:epg,id',
            'stream_icon' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'admin_enabled' => 'boolean',
            'tv_archive' => 'boolean',
            'tv_archive_duration' => 'nullable|integer|min:0|max:720',
        ]);

        $this->streamService->update($stream, $data);

        return redirect()->route('admin.streams.index')->with('success', 'Stream updated successfully.');
    }

    public function destroy(Stream $stream)
    {
        $this->streamService->delete($stream);
        return redirect()->route('admin.streams.index')->with('success', 'Stream deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,enable,disable',
            'ids' => 'required|array',
            'ids.*' => 'exists:streams,id',
        ]);

        $count = $this->streamService->massAction(
            $request->input('action'),
            $request->input('ids')
        );

        return back()->with('success', ucfirst($request->input('action')) . " applied to {$count} streams.");
    }
}
