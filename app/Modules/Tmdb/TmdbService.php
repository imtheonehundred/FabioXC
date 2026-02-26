<?php

namespace App\Modules\Tmdb;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TmdbService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key', '');
    }

    public function search(string $query, string $type = 'movie'): array
    {
        if (empty($this->apiKey)) return [];

        $endpoint = $type === 'tv' ? '/search/tv' : '/search/movie';
        $response = Http::get($this->baseUrl . $endpoint, [
            'api_key' => $this->apiKey,
            'query' => $query,
        ]);

        return $response->successful() ? ($response->json()['results'] ?? []) : [];
    }

    public function getDetails(int $tmdbId, string $type = 'movie'): ?array
    {
        if (empty($this->apiKey)) return null;

        $endpoint = $type === 'tv' ? "/tv/{$tmdbId}" : "/movie/{$tmdbId}";
        $response = Http::get($this->baseUrl . $endpoint, ['api_key' => $this->apiKey, 'append_to_response' => 'credits']);

        return $response->successful() ? $response->json() : null;
    }

    public function onMovieCreated(mixed $payload): void
    {
        if (!isset($payload['tmdb_id']) || empty($payload['tmdb_id'])) return;
        $details = $this->getDetails((int) $payload['tmdb_id']);
        if ($details) {
            DB::table('movies')->where('id', $payload['id'])->update([
                'plot' => $details['overview'] ?? null,
                'rating' => (string) ($details['vote_average'] ?? ''),
                'release_date' => $details['release_date'] ?? null,
            ]);
        }
    }

    public function onSeriesCreated(mixed $payload): void
    {
        if (!isset($payload['tmdb_id']) || empty($payload['tmdb_id'])) return;
        $details = $this->getDetails((int) $payload['tmdb_id'], 'tv');
        if ($details) {
            DB::table('series')->where('id', $payload['id'])->update([
                'plot' => $details['overview'] ?? null,
                'rating' => (string) ($details['vote_average'] ?? ''),
                'release_date' => $details['first_air_date'] ?? null,
            ]);
        }
    }
}
