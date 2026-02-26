<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Vod\MovieService;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\MovieRepository;
use App\Domain\Server\Models\Server;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovieController extends Controller
{
    public function __construct(
        private MovieService $movieService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Movies/Index', [
            'movies' => $this->movieService->list(
                $request->only(['search', 'sort', 'direction', 'category_id']),
                (int) $request->input('per_page', 25)
            ),
            'categories' => $this->movieService->getCategories(),
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Movies/Create', [
            'categories' => $this->movieService->getCategories(),
            'servers' => MovieRepository::getServersList(),
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
        $this->movieService->create($data);
        return redirect()->route('admin.movies.index')->with('success', 'Movie created.');
    }

    public function edit(Movie $movie)
    {
        return Inertia::render('Admin/Movies/Edit', [
            'movie' => $movie,
            'categories' => $this->movieService->getCategories(),
            'servers' => MovieRepository::getServersList(),
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
        $this->movieService->update($movie, $data);
        return redirect()->route('admin.movies.index')->with('success', 'Movie updated.');
    }

    public function destroy(Movie $movie)
    {
        $this->movieService->delete($movie);
        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete,enable,disable', 'ids' => 'required|array', 'ids.*' => 'exists:movies,id']);
        $count = $this->movieService->massAction($request->input('action'), $request->input('ids'));
        return back()->with('success', ucfirst($request->input('action')) . " applied to {$count} movies.");
    }
}
