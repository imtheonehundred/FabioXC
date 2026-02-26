<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Vod\Models\Episode;
use App\Domain\Vod\Models\Series;
use App\Domain\Server\Models\Server;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EpisodeController extends Controller
{
    public function create(Series $series)
    {
        return Inertia::render('Admin/Episodes/Create', [
            'series' => $series,
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
        ]);
    }

    public function store(Request $request, Series $series)
    {
        $data = $request->validate([
            'season_number' => 'required|integer|min:1', 'episode_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255', 'stream_source' => 'required|string',
            'server_id' => 'nullable|exists:servers,id', 'cover' => 'nullable|string',
            'plot' => 'nullable|string', 'duration' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $data['series_id'] = $series->id;
        $data['added'] = now();
        Episode::create($data);
        return redirect()->route('admin.series.show', $series)->with('success', 'Episode added.');
    }

    public function edit(Episode $episode)
    {
        $episode->load('series');
        return Inertia::render('Admin/Episodes/Edit', [
            'episode' => $episode,
            'servers' => Server::orderBy('server_name')->get(['id', 'server_name']),
        ]);
    }

    public function update(Request $request, Episode $episode)
    {
        $data = $request->validate([
            'season_number' => 'required|integer|min:1', 'episode_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255', 'stream_source' => 'required|string',
            'server_id' => 'nullable|exists:servers,id', 'cover' => 'nullable|string',
            'plot' => 'nullable|string', 'duration' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $episode->update($data);
        return redirect()->route('admin.series.show', $episode->series_id)->with('success', 'Episode updated.');
    }

    public function destroy(Episode $episode)
    {
        $seriesId = $episode->series_id;
        $episode->delete();
        return redirect()->route('admin.series.show', $seriesId)->with('success', 'Episode deleted.');
    }

    public function massAction(Request $request)
    {
        $request->validate(['action' => 'required|in:delete', 'ids' => 'required|array', 'ids.*' => 'exists:episodes,id']);
        Episode::whereIn('id', $request->input('ids'))->delete();
        return back()->with('success', 'Episodes deleted.');
    }
}
