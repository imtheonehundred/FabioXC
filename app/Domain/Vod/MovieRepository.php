<?php

namespace App\Domain\Vod;

use App\Domain\Vod\Models\Movie;
use App\Domain\Stream\Models\StreamCategory;
use App\Domain\Server\Models\Server;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    public function __construct(
        private Movie $model
    ) {}

    public function findById(int $id): ?Movie
    {
        return $this->model->with('category')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->with('category');

        if (!empty($filters['search'])) {
            $query->where('stream_display_name', 'like', '%' . $filters['search'] . '%');
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['sort'])) {
            $query->orderBy($filters['sort'], $filters['direction'] ?? 'asc');
        } else {
            $query->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Movie
    {
        $data['added'] = $data['added'] ?? now();
        return $this->model->create($data);
    }

    public function update(Movie $movie, array $data): bool
    {
        return $movie->update($data);
    }

    public function delete(Movie $movie): bool
    {
        return $movie->delete();
    }

    public function massDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function massUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function getCategoriesForMovie(): Collection
    {
        return StreamCategory::where('category_type', 'movie')->orderBy('category_name')->get(['id', 'category_name']);
    }

    public static function getServersList(): Collection
    {
        return Server::orderBy('server_name')->get(['id', 'server_name']);
    }
}
