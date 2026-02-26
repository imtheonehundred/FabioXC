<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Vod\Models\Series;
use App\Domain\Stream\Models\StreamCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Series::with('category')->withCount('episodes');
        if ($search = $request->input('search')) $query->where('title', 'like', "%{$search}%");
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        return Inertia::render('Admin/Series/Index', [
            'series' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'categories' => StreamCategory::where('category_type', 'series')->orderBy('category_name')->get(['id', 'category_name']),
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Series/Create', [
            'categories' => StreamCategory::where('category_type', 'series')->orderBy('category_name')->get(['id', 'category_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'category_id' => 'nullable|exists:stream_categories,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'genre' => 'nullable|string', 'rating' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'release_date' => 'nullable|string', 'youtube_trailer' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $series = Series::create($data);
        return redirect()->route('admin.series.show', $series)->with('success', 'Series created.');
    }

    public function show(Series $series)
    {
        $series->load(['category', 'episodes' => fn ($q) => $q->orderBy('season_number')->orderBy('episode_number')]);
        return Inertia::render('Admin/Series/Show', ['series' => $series]);
    }

    public function edit(Series $series)
    {
        return Inertia::render('Admin/Series/Edit', [
            'series' => $series,
            'categories' => StreamCategory::where('category_type', 'series')->orderBy('category_name')->get(['id', 'category_name']),
        ]);
    }

    public function update(Request $request, Series $series)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'category_id' => 'nullable|exists:stream_categories,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'genre' => 'nullable|string', 'rating' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'release_date' => 'nullable|string', 'youtube_trailer' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $series->update($data);
        return redirect()->route('admin.series.index')->with('success', 'Series updated.');
    }

    public function destroy(Series $series)
    {
        $series->delete();
        return redirect()->route('admin.series.index')->with('success', 'Series deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete,enable,disable', 'ids' => 'required|array', 'ids.*' => 'exists:series,id']);
        match ($request->input('action')) {
            'delete' => Series::whereIn('id', $request->input('ids'))->delete(),
            'enable' => Series::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 1]),
            'disable' => Series::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 0]),
        };
        return back()->with('success', ucfirst($request->input('action')) . " applied.");
    }
}
