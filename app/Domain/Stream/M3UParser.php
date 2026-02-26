<?php

namespace App\Domain\Stream;

use App\Domain\Stream\Models\Stream;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use App\Domain\Line\Models\Line;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Support\Collection;

/**
 * Parse M3U file content for bulk import of streams.
 * Supports EXTINF and optionally EXTGRP; returns array of channel entries.
 */
class M3UParser
{
    /**
     * Parse M3U content into array of entries.
     * Each entry: ['name' => string, 'url' => string, 'group' => string|null, 'tvg_id' => string|null, 'tvg_logo' => string|null]
     *
     * @return array<int, array{name: string, url: string, group: ?string, tvg_id: ?string, tvg_logo: ?string}>
     */
    public function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '#EXTM3U') {
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $current = $this->parseExtinf($line);
                continue;
            }

            if ($current !== null && !str_starts_with($line, '#')) {
                $current['url'] = $line;
                $entries[] = $current;
                $current = null;
            }
        }

        return $entries;
    }

    private function parseExtinf(string $line): array
    {
        $entry = [
            'name' => '',
            'url' => '',
            'group' => null,
            'tvg_id' => null,
            'tvg_logo' => null,
        ];

        if (preg_match('/tvg-name="([^"]*)"/', $line, $m)) {
            $entry['name'] = $m[1];
        }
        if (preg_match('/tvg-id="([^"]*)"/', $line, $m)) {
            $entry['tvg_id'] = $m[1];
        }
        if (preg_match('/tvg-logo="([^"]*)"/', $line, $m)) {
            $entry['tvg_logo'] = $m[1];
        }
        if (preg_match('/group-title="([^"]*)"/', $line, $m)) {
            $entry['group'] = $m[1];
        }
        if (preg_match('/,(.+)$/', $line, $m) && $entry['name'] === '') {
            $entry['name'] = trim($m[1]);
        }

        return $entry;
    }

    /**
     * Parse M3U and return only stream URLs (for simple validation or count).
     *
     * @return array<int, string>
     */
    public function parseUrlsOnly(string $content): array
    {
        $entries = $this->parse($content);
        return array_values(array_filter(array_column($entries, 'url')));
    }
}
