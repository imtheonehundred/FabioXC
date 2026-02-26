<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Bouquet\Models\Bouquet;
use App\Domain\Stream\Models\Stream;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BouquetController extends Controller
{
    public function index(Request $request)
    {
        $query = Bouquet::query();
        if ($search = $request->input('search')) $query->where('bouquet_name', 'like', "%{$search}%");
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'asc'));
        else $query->orderBy('bouquet_order');

        return Inertia::render('Admin/Bouquets/Index', [
            'bouquets' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bouquets/Create', [
            'streams' => Stream::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name', 'type']),
            'movies' => Movie::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name']),
            'series' => Series::where('admin_enabled', 1)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bouquet_name' => 'required|string|max:255',
            'bouquet_channels' => 'nullable|array', 'bouquet_movies' => 'nullable|array',
            'bouquet_series' => 'nullable|array', 'bouquet_radios' => 'nullable|array',
            'bouquet_order' => 'integer|min:0',
        ]);
        Bouquet::create($data);
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet created.');
    }

    public function edit(Bouquet $bouquet)
    {
        return Inertia::render('Admin/Bouquets/Edit', [
            'bouquet' => $bouquet,
            'streams' => Stream::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name', 'type']),
            'movies' => Movie::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name']),
            'series' => Series::where('admin_enabled', 1)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Bouquet $bouquet)
    {
        $data = $request->validate([
            'bouquet_name' => 'required|string|max:255',
            'bouquet_channels' => 'nullable|array', 'bouquet_movies' => 'nullable|array',
            'bouquet_series' => 'nullable|array', 'bouquet_radios' => 'nullable|array',
            'bouquet_order' => 'integer|min:0',
        ]);
        $bouquet->update($data);
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet updated.');
    }

    public function destroy(Bouquet $bouquet)
    {
        $bouquet->delete();
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet deleted.');
    }
}
