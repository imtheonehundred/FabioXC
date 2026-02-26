<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Vod\Models\Movie;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Server\Models\Server;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with('category');
        if ($search = $request->input('search')) {
            $query->where('stream_display_name', 'like', "%{$search}%");
        }
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderByDesc('id');
        }

        return Inertia::render('Admin/Movies/Index', [
            'movies' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'categories' => StreamCategory::where('category_type', 'movie')->orderBy('category_name')->get(['id', 'category_name']),
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Movies/Create', [
            'categories' => StreamCategory::where('category_type', 'movie')->orderBy('category_name')->get(['id', 'category_name']),
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'director' => 'nullable|string', 'genre' => 'nullable|string', 'rating' => 'nullable|string',
            'release_date' => 'nullable|string', 'duration' => 'nullable|string',
            'youtube_trailer' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $data['added'] = now();
        Movie::create($data);
        return redirect()->route('admin.movies.index')->with('success', 'Movie created.');
    }

    public function edit(Movie $movie)
    {
        return Inertia::render('Admin/Movies/Edit', [
            'movie' => $movie,
            'categories' => StreamCategory::where('category_type', 'movie')->orderBy('category_name')->get(['id', 'category_name']),
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
        ]);
    }

    public function update(Request $request, Movie $movie)
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'director' => 'nullable|string', 'genre' => 'nullable|string', 'rating' => 'nullable|string',
            'release_date' => 'nullable|string', 'duration' => 'nullable|string',
            'youtube_trailer' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $movie->update($data);
        return redirect()->route('admin.movies.index')->with('success', 'Movie updated.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete,enable,disable', 'ids' => 'required|array', 'ids.*' => 'exists:movies,id']);
        match ($request->input('action')) {
            'delete' => Movie::whereIn('id', $request->input('ids'))->delete(),
            'enable' => Movie::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 1]),
            'disable' => Movie::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 0]),
        };
        return back()->with('success', ucfirst($request->input('action')) . " applied to " . count($request->input('ids')) . " movies.");
    }
}
