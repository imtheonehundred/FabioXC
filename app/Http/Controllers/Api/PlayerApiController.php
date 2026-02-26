<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Domain\Server\Models\Server;
use App\Domain\Epg\Models\Epg;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            return response()->json(['user_info' => ['auth' => 0, 'message' => 'Credentials required']], 401);
        }

        $line = Line::where('username', $username)->where('password', $password)->first();

        if (!$line) {
            return response()->json(['user_info' => ['auth' => 0, 'message' => 'Invalid credentials']], 401);
        }

        if (!$line->admin_enabled) {
            return response()->json(['user_info' => ['auth' => 0, 'message' => 'Account disabled']], 403);
        }

        if ($line->exp_date && $line->exp_date->isPast()) {
            return response()->json(['user_info' => ['auth' => 0, 'message' => 'Account expired']], 403);
        }

        $server = Server::where('is_main', 1)->first() ?? Server::where('status', 1)->first();

        return response()->json([
            'user_info' => [
                'auth' => 1,
                'username' => $line->username,
                'password' => $line->password,
                'status' => $line->admin_enabled ? 'Active' : 'Disabled',
                'exp_date' => $line->exp_date?->timestamp,
                'is_trial' => (int) $line->is_trial,
                'active_cons' => $line->active_connections,
                'created_at' => $line->added?->timestamp,
                'max_connections' => $line->max_connections,
                'allowed_output_formats' => ['m3u8', 'ts', 'rtmp'],
            ],
            'server_info' => [
                'url' => $server?->domain_name ?? $server?->server_ip,
                'port' => $server?->http_port ?? 80,
                'https_port' => 443,
                'rtmp_port' => $server?->rtmp_port ?? 1935,
                'server_protocol' => 'http',
                'timezone' => config('app.timezone', 'UTC'),
                'timestamp_now' => time(),
            ],
        ]);
    }

    public function getLiveCategories(): JsonResponse
    {
        $categories = StreamCategory::where('category_type', 'live')
            ->orderBy('cat_order')
            ->get()
            ->map(fn ($c) => [
                'category_id' => (string) $c->id,
                'category_name' => $c->category_name,
                'parent_id' => $c->parent_id ?? 0,
            ]);

        return response()->json($categories);
    }

    public function getLiveStreams(Request $request): JsonResponse
    {
        $query = Stream::where('type', 'live')->where('admin_enabled', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $streams = $query->orderBy('stream_order')->get()->map(fn ($s) => [
            'num' => $s->id,
            'name' => $s->stream_display_name,
            'stream_type' => $s->type,
            'stream_id' => $s->id,
            'stream_icon' => $s->stream_icon ?? '',
            'epg_channel_id' => $s->epg_id,
            'added' => $s->added?->timestamp,
            'category_id' => (string) ($s->category_id ?? ''),
            'is_adult' => 0,
            'custom_sid' => null,
            'tv_archive' => 0,
            'direct_source' => '',
            'tv_archive_duration' => 0,
        ]);

        return response()->json($streams);
    }

    public function getVodCategories(): JsonResponse
    {
        $categories = StreamCategory::where('category_type', 'movie')
            ->orderBy('cat_order')
            ->get()
            ->map(fn ($c) => [
                'category_id' => (string) $c->id,
                'category_name' => $c->category_name,
                'parent_id' => $c->parent_id ?? 0,
            ]);

        return response()->json($categories);
    }

    public function getVodStreams(Request $request): JsonResponse
    {
        $query = Movie::where('admin_enabled', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $movies = $query->orderByDesc('id')->get()->map(fn ($m) => [
            'num' => $m->id,
            'name' => $m->stream_display_name,
            'stream_type' => 'movie',
            'stream_id' => $m->id,
            'stream_icon' => $m->cover ?? '',
            'rating' => $m->rating ?? '',
            'rating_5based' => $m->rating_5based ?? 0,
            'added' => $m->added?->timestamp,
            'category_id' => (string) ($m->category_id ?? ''),
            'container_extension' => $m->container_extension ?? 'mp4',
            'direct_source' => '',
        ]);

        return response()->json($movies);
    }

    public function getVodInfo(Request $request): JsonResponse
    {
        $movie = Movie::find($request->input('vod_id'));

        if (!$movie) {
            return response()->json(['info' => []], 404);
        }

        return response()->json([
            'info' => [
                'movie_image' => $movie->cover ?? '',
                'tmdb_id' => $movie->tmdb_id ?? '',
                'name' => $movie->stream_display_name,
                'o_name' => $movie->stream_display_name,
                'plot' => $movie->plot ?? '',
                'cast' => $movie->cast ?? '',
                'director' => $movie->director ?? '',
                'genre' => $movie->genre ?? '',
                'release_date' => $movie->release_date ?? '',
                'duration' => $movie->duration ?? '',
                'duration_secs' => 0,
                'rating' => $movie->rating ?? '',
                'youtube_trailer' => $movie->youtube_trailer ?? '',
                'container_extension' => $movie->container_extension ?? 'mp4',
            ],
            'movie_data' => [
                'stream_id' => $movie->id,
                'name' => $movie->stream_display_name,
                'added' => $movie->added?->timestamp,
                'category_id' => (string) ($movie->category_id ?? ''),
                'container_extension' => $movie->container_extension ?? 'mp4',
            ],
        ]);
    }

    public function getSeriesCategories(): JsonResponse
    {
        $categories = StreamCategory::where('category_type', 'series')
            ->orderBy('cat_order')
            ->get()
            ->map(fn ($c) => [
                'category_id' => (string) $c->id,
                'category_name' => $c->category_name,
                'parent_id' => $c->parent_id ?? 0,
            ]);

        return response()->json($categories);
    }

    public function getSeries(Request $request): JsonResponse
    {
        $query = Series::where('admin_enabled', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $series = $query->orderByDesc('id')->get()->map(fn ($s) => [
            'num' => $s->id,
            'name' => $s->title,
            'series_id' => $s->id,
            'cover' => $s->cover ?? '',
            'plot' => $s->plot ?? '',
            'cast' => $s->cast ?? '',
            'genre' => $s->genre ?? '',
            'release_date' => $s->release_date ?? '',
            'rating' => $s->rating ?? '',
            'rating_5based' => $s->rating_5based ?? 0,
            'category_id' => (string) ($s->category_id ?? ''),
            'last_modified' => $s->last_modified,
            'youtube_trailer' => $s->youtube_trailer ?? '',
            'tmdb_id' => $s->tmdb_id ?? '',
        ]);

        return response()->json($series);
    }

    public function getSeriesInfo(Request $request): JsonResponse
    {
        $series = Series::with('episodes')->find($request->input('series_id'));

        if (!$series) {
            return response()->json(['episodes' => [], 'info' => []], 404);
        }

        $episodes = [];
        foreach ($series->episodes as $ep) {
            $seasonKey = (string) $ep->season_number;
            if (!isset($episodes[$seasonKey])) {
                $episodes[$seasonKey] = [];
            }
            $episodes[$seasonKey][] = [
                'id' => (string) $ep->id,
                'episode_num' => $ep->episode_number,
                'title' => $ep->title ?? "Episode {$ep->episode_number}",
                'container_extension' => $ep->container_extension ?? 'mp4',
                'info' => [
                    'name' => $ep->title ?? "Episode {$ep->episode_number}",
                    'plot' => $ep->plot ?? '',
                    'duration' => $ep->duration ?? '',
                    'movie_image' => $ep->cover ?? '',
                    'rating' => $ep->rating ?? '',
                    'season' => $ep->season_number,
                ],
                'season' => $ep->season_number,
                'added' => $ep->added?->timestamp,
                'direct_source' => '',
            ];
        }

        return response()->json([
            'seasons' => collect($episodes)->keys()->map(fn ($s) => [
                'season_number' => (int) $s,
                'name' => "Season {$s}",
                'cover' => $series->cover ?? '',
            ])->values(),
            'info' => [
                'name' => $series->title,
                'cover' => $series->cover ?? '',
                'plot' => $series->plot ?? '',
                'cast' => $series->cast ?? '',
                'genre' => $series->genre ?? '',
                'release_date' => $series->release_date ?? '',
                'rating' => $series->rating ?? '',
                'youtube_trailer' => $series->youtube_trailer ?? '',
                'tmdb_id' => $series->tmdb_id ?? '',
                'category_id' => (string) ($series->category_id ?? ''),
            ],
            'episodes' => $episodes,
        ]);
    }

    public function getEpg(Request $request): JsonResponse
    {
        $streamId = $request->input('stream_id');
        // EPG data would come from parsed XML — return empty structure for now
        return response()->json([
            'epg_listings' => [],
        ]);
    }

    public function xmltv(Request $request)
    {
        $output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $output .= '<tv generator-info-name="XC_VM">' . "\n";

        $streams = Stream::where('type', 'live')
            ->where('admin_enabled', 1)
            ->whereNotNull('epg_id')
            ->get();

        foreach ($streams as $stream) {
            $output .= '  <channel id="' . $stream->id . '">' . "\n";
            $output .= '    <display-name>' . htmlspecialchars($stream->stream_display_name) . '</display-name>' . "\n";
            if ($stream->stream_icon) {
                $output .= '    <icon src="' . htmlspecialchars($stream->stream_icon) . '" />' . "\n";
            }
            $output .= '  </channel>' . "\n";
        }

        $output .= '</tv>';

        return response($output, 200)->header('Content-Type', 'application/xml');
    }
}
