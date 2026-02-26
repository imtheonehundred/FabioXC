<?php

namespace App\Domain\Bouquet;

use App\Domain\Bouquet\Models\Bouquet;
use App\Domain\Stream\Models\Stream;
use App\Domain\Vod\Models\Movie;
use App\Domain\Vod\Models\Series;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BouquetRepository
{
    public function __construct(
        private Bouquet $model
    ) {}

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->query();
        if (!empty($filters['search'])) {
            $query->where('bouquet_name', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderBy('bouquet_order');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Bouquet
    {
        return $this->model->create($data);
    }

    public function update(Bouquet $bouquet, array $data): bool
    {
        return $bouquet->update($data);
    }

    public function delete(Bouquet $bouquet): bool
    {
        return $bouquet->delete();
    }

    public function getStreamsForBouquet(): Collection
    {
        return Stream::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name', 'type']);
    }

    public function getMoviesForBouquet(): Collection
    {
        return Movie::where('admin_enabled', 1)->orderBy('stream_display_name')->get(['id', 'stream_display_name']);
    }

    public function getSeriesForBouquet(): Collection
    {
        return Series::where('admin_enabled', 1)->orderBy('title')->get(['id', 'title']);
    }
}
