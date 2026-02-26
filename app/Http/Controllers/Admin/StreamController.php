<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Server\Models\Server;
use App\Domain\Epg\Models\Epg;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StreamController extends Controller
{
    public function index(Request $request)
    {
        $query = Stream::with(['category', 'server']);

        if ($search = $request->input('search')) {
            $query->where('stream_display_name', 'like', "%{$search}%");
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        $streams = $query->paginate($request->input('per_page', 25))->withQueryString();
        $categories = StreamCategory::where('category_type', 'live')->orderBy('category_name')->get(['id', 'category_name']);

        return Inertia::render('Admin/Streams/Index', [
            'streams' => $streams,
            'categories' => $categories,
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id', 'status', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Streams/Create', [
            'categories' => StreamCategory::where('category_type', 'live')->orderBy('category_name')->get(['id', 'category_name']),
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
        ]);

        $data['added'] = now();
        Stream::create($data);

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
            'categories' => StreamCategory::where('category_type', 'live')->orderBy('category_name')->get(['id', 'category_name']),
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
        ]);

        $stream->update($data);

        return redirect()->route('admin.streams.index')->with('success', 'Stream updated successfully.');
    }

    public function destroy(Stream $stream)
    {
        $stream->delete();
        return redirect()->route('admin.streams.index')->with('success', 'Stream deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,enable,disable',
            'ids' => 'required|array',
            'ids.*' => 'exists:streams,id',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        match ($action) {
            'delete' => Stream::whereIn('id', $ids)->delete(),
            'enable' => Stream::whereIn('id', $ids)->update(['admin_enabled' => 1]),
            'disable' => Stream::whereIn('id', $ids)->update(['admin_enabled' => 0]),
        };

        return back()->with('success', ucfirst($action) . " applied to " . count($ids) . " streams.");
    }
}
