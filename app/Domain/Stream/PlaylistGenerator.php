<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Domain\Line\Models\Line;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Support\Collection;

/**
 * Generate M3U/EPG-style playlists for live, VOD, series, and radio.
 * Used for player API and bouquet-based line playlists.
 */
class PlaylistGenerator
{
    public function __construct(
        private string $baseUrl = ''
    ) {
        if ($this->baseUrl === '') {
            $this->baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
        }
    }

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Generate M3U content for a line (username/password) with given streams, movies, series.
     *
     * @param string $username
     * @param string $password
     * @param Collection<int, Stream>|null $streams
     * @param Collection<int, Movie>|null $movies
     * @param Collection<int, array{id: int, title: string, episodes: array}>|null $seriesWithEpisodes
     * @param bool $includeRadio
     */
    public function generateForLine(
        string $username,
        string $password,
        ?Collection $streams = null,
        ?Collection $movies = null,
        ?Collection $seriesWithEpisodes = null,
        bool $includeRadio = true
    ): string {
        $lines = ["#EXTM3U"];
        $streams = $streams ?? collect();
        $movies = $movies ?? collect();
        $seriesWithEpisodes = $seriesWithEpisodes ?? collect();

        foreach ($streams as $stream) {
            if (!$includeRadio && ($stream->type ?? '') === 'radio') {
                continue;
            }
            $url = "{$this->baseUrl}/live/{$username}/{$password}/{$stream->id}.m3u8";
            $lines[] = sprintf(
                '#EXTINF:-1 tvg-id="%s" tvg-name="%s" tvg-logo="%s" group-title="%s",%s',
                (string) ($stream->epg_id ?? $stream->id),
                $this->escape($stream->stream_display_name ?? 'Stream ' . $stream->id),
                $this->escape($stream->stream_icon ?? ''),
                $this->escape($stream->category?->category_name ?? 'Live'),
                $stream->stream_display_name ?? 'Stream ' . $stream->id
            );
            $lines[] = $url;
        }

        foreach ($movies as $movie) {
            $ext = $movie->container_extension ?? 'mp4';
            $url = "{$this->baseUrl}/movie/{$username}/{$password}/{$movie->id}.{$ext}";
            $lines[] = sprintf(
                '#EXTINF:-1 tvg-id="%s" tvg-logo="%s" group-title="VOD",%s',
                (string) $movie->id,
                $this->escape($movie->cover ?? ''),
                $movie->stream_display_name ?? 'Movie ' . $movie->id
            );
            $lines[] = $url;
        }

        foreach ($seriesWithEpisodes as $series) {
            $title = $series['title'] ?? 'Series';
            $episodes = $series['episodes'] ?? [];
            foreach ($episodes as $ep) {
                $epId = $ep['id'] ?? 0;
                $epTitle = $ep['title'] ?? "Episode {$epId}";
                $url = "{$this->baseUrl}/series/{$username}/{$password}/{$epId}.mp4";
                $lines[] = sprintf(
                    '#EXTINF:-1 group-title="%s",%s',
                    $this->escape($title),
                    $epTitle
                );
                $lines[] = $url;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Generate M3U for a bouquet (channel IDs, movie IDs, series data).
     */
    public function generateForBouquet(
        Bouquet $bouquet,
        string $username,
        string $password,
        Collection $streams,
        Collection $movies,
        Collection $seriesWithEpisodes
    ): string {
        $channelIds = $bouquet->bouquet_channels ?? [];
        $movieIds = $bouquet->bouquet_movies ?? [];
        $seriesIds = $bouquet->bouquet_series ?? [];

        $streams = $streams->whereIn('id', $channelIds)->values();
        $movies = $movies->whereIn('id', $movieIds)->values();
        $filtered = $seriesWithEpisodes->whereIn('id', $seriesIds)->values();

        return $this->generateForLine($username, $password, $streams, $movies, $filtered, true);
    }

    /**
     * Generate simple live-only M3U (all enabled streams).
     */
    public function generateLiveOnly(string $username, string $password, Collection $streams): string
    {
        $lines = ["#EXTM3U"];
        foreach ($streams as $stream) {
            $url = "{$this->baseUrl}/live/{$username}/{$password}/{$stream->id}.m3u8";
            $group = $stream->category?->category_name ?? 'Live';
            $lines[] = sprintf(
                '#EXTINF:-1 tvg-id="%s" tvg-name="%s" tvg-logo="%s" group-title="%s",%s',
                (string) ($stream->epg_id ?? $stream->id),
                $this->escape($stream->stream_display_name ?? ''),
                $this->escape($stream->stream_icon ?? ''),
                $this->escape($group),
                $stream->stream_display_name ?? 'Stream ' . $stream->id
            );
            $lines[] = $url;
        }
        return implode("\n", $lines);
    }

    private function escape(string $s): string
    {
        return str_replace(['"', "\n", "\r"], ['\'', ' ', ' '], $s);
    }
}
