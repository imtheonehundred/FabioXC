<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Domain\Vod\Models\Episode;
use App\Domain\Line\Models\Line;
use App\Domain\Line\Models\Package;
use App\Domain\Server\Models\Server;
use App\Domain\Bouquet\Models\Bouquet;
use App\Domain\Epg\Models\Epg;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminApiController extends Controller
{
    // ─── Streams ──────────────────────────────────────────

    public function listStreams(Request $request): JsonResponse
    {
        $query = Stream::with(['category', 'server']);
        if ($request->filled('search')) $query->where('stream_display_name', 'like', '%' . $request->input('search') . '%');
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('type')) $query->where('type', $request->input('type'));
        return response()->json($query->orderByDesc('id')->paginate($request->input('per_page', 25)));
    }

    public function getStream(int $id): JsonResponse
    {
        return response()->json(Stream::with(['category', 'server', 'epg'])->findOrFail($id));
    }

    public function createStream(Request $request): JsonResponse
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'type' => 'required|in:live,created,radio',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'epg_id' => 'nullable|exists:epg,id',
            'stream_icon' => 'nullable|string',
            'notes' => 'nullable|string',
            'admin_enabled' => 'boolean',
        ]);
        $data['added'] = now();
        $stream = Stream::create($data);
        return response()->json($stream, 201);
    }

    public function updateStream(Request $request, int $id): JsonResponse
    {
        $stream = Stream::findOrFail($id);
        $data = $request->validate([
            'stream_display_name' => 'sometimes|string|max:255',
            'stream_source' => 'sometimes|string',
            'type' => 'sometimes|in:live,created,radio',
            'category_id' => 'nullable|exists:stream_categories,id',
            'server_id' => 'nullable|exists:servers,id',
            'epg_id' => 'nullable|exists:epg,id',
            'stream_icon' => 'nullable|string',
            'notes' => 'nullable|string',
            'admin_enabled' => 'boolean',
        ]);
        $stream->update($data);
        return response()->json($stream->fresh());
    }

    public function deleteStream(int $id): JsonResponse
    {
        Stream::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function massActionStreams(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,enable,disable',
            'ids' => 'required|array',
            'ids.*' => 'exists:streams,id',
        ]);
        match ($request->input('action')) {
            'delete' => Stream::whereIn('id', $request->input('ids'))->delete(),
            'enable' => Stream::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 1]),
            'disable' => Stream::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 0]),
        };
        return response()->json(['message' => 'Action applied', 'count' => count($request->input('ids'))]);
    }

    // ─── Categories ───────────────────────────────────────

    public function listCategories(Request $request): JsonResponse
    {
        $query = StreamCategory::withCount('streams');
        if ($request->filled('category_type')) $query->where('category_type', $request->input('category_type'));
        return response()->json($query->orderBy('cat_order')->paginate($request->input('per_page', 100)));
    }

    public function getCategory(int $id): JsonResponse
    {
        return response()->json(StreamCategory::withCount('streams')->findOrFail($id));
    }

    public function createCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:live,movie,series,radio',
            'parent_id' => 'nullable|exists:stream_categories,id',
            'cat_order' => 'integer|min:0',
        ]);
        return response()->json(StreamCategory::create($data), 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $cat = StreamCategory::findOrFail($id);
        $cat->update($request->validate([
            'category_name' => 'sometimes|string|max:255',
            'category_type' => 'sometimes|in:live,movie,series,radio',
            'parent_id' => 'nullable|exists:stream_categories,id',
            'cat_order' => 'integer|min:0',
        ]));
        return response()->json($cat->fresh());
    }

    public function deleteCategory(int $id): JsonResponse
    {
        StreamCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Movies ───────────────────────────────────────────

    public function listMovies(Request $request): JsonResponse
    {
        $query = Movie::with('category');
        if ($request->filled('search')) $query->where('stream_display_name', 'like', '%' . $request->input('search') . '%');
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        return response()->json($query->orderByDesc('id')->paginate($request->input('per_page', 25)));
    }

    public function getMovie(int $id): JsonResponse
    {
        return response()->json(Movie::with('category')->findOrFail($id));
    }

    public function createMovie(Request $request): JsonResponse
    {
        $data = $request->validate([
            'stream_display_name' => 'required|string|max:255', 'stream_source' => 'required|string',
            'category_id' => 'nullable|exists:stream_categories,id', 'server_id' => 'nullable|exists:servers,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'director' => 'nullable|string', 'genre' => 'nullable|string', 'rating' => 'nullable|string',
            'release_date' => 'nullable|string', 'duration' => 'nullable|string',
            'tmdb_id' => 'nullable|string', 'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $data['added'] = now();
        return response()->json(Movie::create($data), 201);
    }

    public function updateMovie(Request $request, int $id): JsonResponse
    {
        $movie = Movie::findOrFail($id);
        $movie->update($request->validate([
            'stream_display_name' => 'sometimes|string|max:255', 'stream_source' => 'sometimes|string',
            'category_id' => 'nullable|exists:stream_categories,id', 'server_id' => 'nullable|exists:servers,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'director' => 'nullable|string', 'genre' => 'nullable|string', 'rating' => 'nullable|string',
            'release_date' => 'nullable|string', 'duration' => 'nullable|string',
            'tmdb_id' => 'nullable|string', 'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]));
        return response()->json($movie->fresh());
    }

    public function deleteMovie(int $id): JsonResponse
    {
        Movie::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Series ───────────────────────────────────────────

    public function listSeries(Request $request): JsonResponse
    {
        $query = Series::with('category')->withCount('episodes');
        if ($request->filled('search')) $query->where('title', 'like', '%' . $request->input('search') . '%');
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        return response()->json($query->orderByDesc('id')->paginate($request->input('per_page', 25)));
    }

    public function getSeries(int $id): JsonResponse
    {
        return response()->json(Series::with(['category', 'episodes'])->findOrFail($id));
    }

    public function createSeries(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'category_id' => 'nullable|exists:stream_categories,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'genre' => 'nullable|string', 'rating' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'release_date' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        return response()->json(Series::create($data), 201);
    }

    public function updateSeries(Request $request, int $id): JsonResponse
    {
        $series = Series::findOrFail($id);
        $series->update($request->validate([
            'title' => 'sometimes|string|max:255', 'category_id' => 'nullable|exists:stream_categories,id',
            'cover' => 'nullable|string', 'plot' => 'nullable|string', 'cast' => 'nullable|string',
            'genre' => 'nullable|string', 'rating' => 'nullable|string', 'tmdb_id' => 'nullable|string',
            'release_date' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]));
        return response()->json($series->fresh());
    }

    public function deleteSeries(int $id): JsonResponse
    {
        Series::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Episodes ─────────────────────────────────────────

    public function listEpisodes(int $seriesId): JsonResponse
    {
        $episodes = Episode::where('series_id', $seriesId)
            ->orderBy('season_number')->orderBy('episode_number')->get();
        return response()->json($episodes);
    }

    public function createEpisode(Request $request, int $seriesId): JsonResponse
    {
        Series::findOrFail($seriesId);
        $data = $request->validate([
            'season_number' => 'required|integer|min:1', 'episode_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255', 'stream_source' => 'required|string',
            'server_id' => 'nullable|exists:servers,id', 'cover' => 'nullable|string',
            'plot' => 'nullable|string', 'duration' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]);
        $data['series_id'] = $seriesId;
        $data['added'] = now();
        return response()->json(Episode::create($data), 201);
    }

    public function updateEpisode(Request $request, int $id): JsonResponse
    {
        $ep = Episode::findOrFail($id);
        $ep->update($request->validate([
            'season_number' => 'sometimes|integer|min:1', 'episode_number' => 'sometimes|integer|min:1',
            'title' => 'nullable|string|max:255', 'stream_source' => 'sometimes|string',
            'server_id' => 'nullable|exists:servers,id', 'cover' => 'nullable|string',
            'plot' => 'nullable|string', 'duration' => 'nullable|string',
            'container_extension' => 'nullable|string', 'admin_enabled' => 'boolean',
        ]));
        return response()->json($ep->fresh());
    }

    public function deleteEpisode(int $id): JsonResponse
    {
        Episode::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Lines ────────────────────────────────────────────

    public function listLines(Request $request): JsonResponse
    {
        $query = Line::with('packages');
        if ($request->filled('search')) $query->where('username', 'like', '%' . $request->input('search') . '%');
        return response()->json($query->orderByDesc('id')->paginate($request->input('per_page', 25)));
    }

    public function getLine(int $id): JsonResponse
    {
        return response()->json(Line::with('packages')->findOrFail($id));
    }

    public function createLine(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|unique:lines,username|max:255',
            'password' => 'required|string|max:255',
            'exp_date' => 'nullable|date', 'max_connections' => 'required|integer|min:1',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array', 'package_ids.*' => 'exists:packages,id',
        ]);
        $packageIds = $data['package_ids'] ?? [];
        unset($data['package_ids']);
        $data['added'] = now();
        $data['created_by'] = $request->user()?->id;
        $line = Line::create($data);
        if ($packageIds) $line->packages()->attach($packageIds);
        return response()->json($line->load('packages'), 201);
    }

    public function updateLine(Request $request, int $id): JsonResponse
    {
        $line = Line::findOrFail($id);
        $data = $request->validate([
            'username' => "sometimes|string|unique:lines,username,{$id}|max:255",
            'password' => 'sometimes|string|max:255',
            'exp_date' => 'nullable|date', 'max_connections' => 'sometimes|integer|min:1',
            'is_trial' => 'boolean', 'admin_enabled' => 'boolean',
            'bouquet' => 'nullable|array', 'notes' => 'nullable|string',
            'package_ids' => 'nullable|array', 'package_ids.*' => 'exists:packages,id',
        ]);
        if (isset($data['package_ids'])) {
            $line->packages()->sync($data['package_ids']);
            unset($data['package_ids']);
        }
        $line->update($data);
        return response()->json($line->fresh()->load('packages'));
    }

    public function deleteLine(int $id): JsonResponse
    {
        Line::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function massActionLines(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,enable,disable',
            'ids' => 'required|array', 'ids.*' => 'exists:lines,id',
        ]);
        match ($request->input('action')) {
            'delete' => Line::whereIn('id', $request->input('ids'))->delete(),
            'enable' => Line::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 1]),
            'disable' => Line::whereIn('id', $request->input('ids'))->update(['admin_enabled' => 0]),
        };
        return response()->json(['message' => 'Action applied', 'count' => count($request->input('ids'))]);
    }

    // ─── Users ────────────────────────────────────────────

    public function listUsers(Request $request): JsonResponse
    {
        $query = User::with('group');
        if ($request->filled('search')) $query->where('username', 'like', '%' . $request->input('search') . '%');
        return response()->json($query->orderByDesc('id')->paginate($request->input('per_page', 25)));
    }

    public function getUser(int $id): JsonResponse
    {
        return response()->json(User::with('group')->findOrFail($id));
    }

    public function createUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:6', 'email' => 'nullable|email',
            'member_group_id' => 'nullable|exists:member_groups,id',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['api_key'] = \Illuminate\Support\Str::random(48);
        return response()->json(User::create($data), 201);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'username' => "sometimes|string|unique:users,username,{$id}|max:255",
            'password' => 'nullable|string|min:6', 'email' => 'nullable|email',
            'member_group_id' => 'nullable|exists:member_groups,id',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json($user->fresh());
    }

    public function deleteUser(int $id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Servers ──────────────────────────────────────────

    public function listServers(Request $request): JsonResponse
    {
        return response()->json(Server::withCount('streams')->orderByDesc('is_main')->get());
    }

    public function getServer(int $id): JsonResponse
    {
        return response()->json(Server::withCount('streams')->findOrFail($id));
    }

    public function createServer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'server_name' => 'required|string|max:255', 'server_ip' => 'required|string',
            'domain_name' => 'nullable|string', 'http_port' => 'required|integer',
            'rtmp_port' => 'required|integer',
        ]);
        $data['server_key'] = \Illuminate\Support\Str::random(48);
        return response()->json(Server::create($data), 201);
    }

    public function updateServer(Request $request, int $id): JsonResponse
    {
        $server = Server::findOrFail($id);
        $server->update($request->validate([
            'server_name' => 'sometimes|string|max:255', 'server_ip' => 'sometimes|string',
            'domain_name' => 'nullable|string', 'http_port' => 'sometimes|integer',
            'rtmp_port' => 'sometimes|integer',
        ]));
        return response()->json($server->fresh());
    }

    public function deleteServer(int $id): JsonResponse
    {
        Server::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Bouquets ─────────────────────────────────────────

    public function listBouquets(): JsonResponse
    {
        return response()->json(Bouquet::orderBy('bouquet_order')->get());
    }

    public function getBouquet(int $id): JsonResponse
    {
        return response()->json(Bouquet::findOrFail($id));
    }

    public function createBouquet(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bouquet_name' => 'required|string|max:255',
            'bouquet_channels' => 'nullable', 'bouquet_movies' => 'nullable',
            'bouquet_series' => 'nullable', 'bouquet_radios' => 'nullable',
            'bouquet_order' => 'integer|min:0',
        ]);
        return response()->json(Bouquet::create($data), 201);
    }

    public function updateBouquet(Request $request, int $id): JsonResponse
    {
        $bouquet = Bouquet::findOrFail($id);
        $bouquet->update($request->validate([
            'bouquet_name' => 'sometimes|string|max:255',
            'bouquet_channels' => 'nullable', 'bouquet_movies' => 'nullable',
            'bouquet_series' => 'nullable', 'bouquet_radios' => 'nullable',
            'bouquet_order' => 'integer|min:0',
        ]));
        return response()->json($bouquet->fresh());
    }

    public function deleteBouquet(int $id): JsonResponse
    {
        Bouquet::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Packages ─────────────────────────────────────────

    public function listPackages(): JsonResponse
    {
        return response()->json(Package::withCount('lines')->get());
    }

    public function getPackage(int $id): JsonResponse
    {
        return response()->json(Package::withCount('lines')->findOrFail($id));
    }

    public function createPackage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_name' => 'required|string|max:255',
            'is_trial' => 'boolean', 'is_official' => 'boolean', 'is_addon' => 'boolean',
        ]);
        return response()->json(Package::create($data), 201);
    }

    public function updatePackage(Request $request, int $id): JsonResponse
    {
        $pkg = Package::findOrFail($id);
        $pkg->update($request->validate([
            'package_name' => 'sometimes|string|max:255',
            'is_trial' => 'boolean', 'is_official' => 'boolean', 'is_addon' => 'boolean',
        ]));
        return response()->json($pkg->fresh());
    }

    public function deletePackage(int $id): JsonResponse
    {
        Package::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── EPG ──────────────────────────────────────────────

    public function listEpg(): JsonResponse
    {
        return response()->json(Epg::all());
    }

    public function getEpg(int $id): JsonResponse
    {
        return response()->json(Epg::findOrFail($id));
    }

    public function createEpg(Request $request): JsonResponse
    {
        $data = $request->validate([
            'epg_name' => 'required|string|max:255', 'epg_url' => 'required|string',
        ]);
        return response()->json(Epg::create($data), 201);
    }

    public function updateEpg(Request $request, int $id): JsonResponse
    {
        $epg = Epg::findOrFail($id);
        $epg->update($request->validate([
            'epg_name' => 'sometimes|string|max:255', 'epg_url' => 'sometimes|string',
        ]));
        return response()->json($epg->fresh());
    }

    public function deleteEpg(int $id): JsonResponse
    {
        Epg::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ─── Settings ─────────────────────────────────────────

    public function getSettings(): JsonResponse
    {
        return response()->json(DB::table('settings')->pluck('value', 'key'));
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate(['settings' => 'required|array', 'settings.*' => 'nullable|string|max:1000']);
        foreach ($request->input('settings') as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        }
        return response()->json(['message' => 'Settings updated']);
    }
}
